<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Grading;
use App\Models\Groups;
use App\Models\Position;
use App\Models\SkLetter;
use App\Models\Stores;
use App\Services\UploadStorage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;

/**
 * Kontrak kerja karyawan.
 *
 * Akses (route group: ManageContracts | VEUSomeContracts):
 * - Melihat daftar, detail, dokumen: keduanya.
 * - Membuat, mengubah, mengakhiri: ManageContracts (Admin, HeadHR).
 * - Membuat kontrak juga meminta konfirmasi password (berlaku 30 menit) karena memuat gaji.
 *
 * Status: Active (berlaku) → Expired otomatis setelah tanggal berakhir lewat (contracts:expire, harian),
 * atau Terminated kalau diakhiri lebih awal. Satu karyawan hanya boleh punya satu kontrak Active.
 */
class ContractController extends Controller
{
    private const PASSWORD_WINDOW_MINUTES = 30;
    private const ENDING_SOON_DAYS = 30;

    public function __construct(private \App\Services\ContractService $service)
    {
    }

    private function canManage(): bool
    {
        return (bool) auth()->user()?->can('ManageContracts');
    }

    /** Pemegang permission modul kontrak (melihat semua). */
    private function canViewAll(): bool
    {
        return (bool) auth()->user()?->canAny(['ManageContracts', 'VEUSomeContracts']);
    }

    private function me(): ?Employee
    {
        return auth()->user()?->employee;
    }

    private function isApproverOf(Contract $c): bool
    {
        $me = $this->me();
        return $me && in_array($me->id, array_filter([$c->approver_2, $c->approver_3]), true);
    }

    private function authorizeView(Contract $c): void
    {
        abort_unless($this->canViewAll() || $this->isApproverOf($c), 403, 'Anda tidak terlibat dalam kontrak ini.');
    }

    /** Kueri "giliran saya": kontrak Pending yang sedang menunggu saya. */
    private function myTurnScope($q, Employee $me)
    {
        return $q->where('contracts.contract_status', 'Pending')->where(fn($w) => $w
            ->where(fn($x) => $x->whereNull('contracts.approver_2_at')->where('contracts.approver_2', $me->id))
            ->orWhere(fn($x) => $x->whereNotNull('contracts.approver_2_at')->whereNull('contracts.approver_3_at')->where('contracts.approver_3', $me->id)));
    }

    private function passwordConfirmed(): bool
    {
        $at = session('contract_password_confirmed_at');
        return $at && Carbon::parse($at)->diffInMinutes(now()) < self::PASSWORD_WINDOW_MINUTES;
    }

    public function index()
    {
        $me = $this->me();
        $viewAll = $this->canViewAll();
        // Tanpa permission modul: hanya approver kontrak yang bisa membuka daftar (kontrak yang menyangkut dirinya)
        abort_unless($viewAll || ($me && Contract::where('approver_2', $me->id)->orWhere('approver_3', $me->id)->exists()), 403);

        $active = Contract::where('contract_status', 'Active');
        return view('pages.contract.contract', [
            'canManage'      => $this->canManage(),
            'viewAll'        => $viewAll,
            'stats'          => [
                'my_turn'     => $me ? $this->myTurnScope(Contract::query(), $me)->count() : 0,
                'pending'     => Contract::where('contract_status', 'Pending')->count(),
                'draft'       => Contract::where('contract_status', 'Draft')->count(),
                'active'      => (clone $active)->count(),
                'ending_soon' => (clone $active)->whereNotNull('end_date')->whereBetween('end_date', [today(), today()->addDays(self::ENDING_SOON_DAYS)])->count(),
                'expired'     => Contract::where('contract_status', 'Expired')->count(),
                'terminated'  => Contract::where('contract_status', 'Terminated')->count(),
                // Karyawan aktif yang belum punya kontrak berlaku
                'without'     => Employee::where('status', 'Active')->whereDoesntHave('contracts', fn($q) => $q->where('contract_status', 'Active'))->count(),
            ],
            'endingSoonDays' => self::ENDING_SOON_DAYS,
            'types'          => Contract::getContractTypeOptions(),
            'companies'      => Company::orderBy('name')->pluck('name', 'id'),
            'departments'    => Departments::orderBy('department_name')->pluck('department_name', 'id'),
            'stores'         => Stores::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function getContracts(Request $request)
    {
        $query = Contract::query()
            ->leftJoin('employees_tables as e', 'e.id', '=', 'contracts.employee_id')
            ->leftJoin('position_tables as p', 'p.id', '=', DB::raw('COALESCE(contracts.position_id, e.position_id)'))
            ->leftJoin('departments_tables as d', 'd.id', '=', DB::raw('COALESCE(contracts.department_id, e.department_id)'))
            ->leftJoin('stores_tables as s', 's.id', '=', DB::raw('COALESCE(contracts.store_id, e.store_id)'))
            ->leftJoin('company_tables as c', 'c.id', '=', DB::raw('COALESCE(contracts.issuer_company_id, e.company_id)'))
            ->leftJoin('employees_tables as a2', 'a2.id', '=', 'contracts.approver_2')
            ->leftJoin('employees_tables as a3', 'a3.id', '=', 'contracts.approver_3')
            ->select([
                'contracts.id', 'contracts.contract_type', 'contracts.contract_status', 'contracts.start_date', 'contracts.end_date',
                'contracts.sk_letter_id', 'contracts.file_path', 'contracts.terminated_at', 'contracts.created_at', 'contracts.returned_at',
                'contracts.approver_2', 'contracts.approver_3', 'contracts.approver_2_at', 'contracts.approver_3_at',
                'a2.employee_name as approver_2_name', 'a3.employee_name as approver_3_name',
                'e.employee_name', 'e.employee_pengenal', 'e.status as employee_status',
                'p.name as position_name', 'd.department_name', 's.name as store_name', 'c.name as company_name',
            ]);

        $me = $this->me();
        if (!$this->canViewAll()) {
            abort_unless($me, 403);
            $query->where(fn($q) => $q->where('contracts.approver_2', $me->id)->orWhere('contracts.approver_3', $me->id));
        }

        $query->when($request->filled('filter_company'), fn($q) => $q->where('c.id', $request->filter_company))
            ->when($request->filled('filter_department'), fn($q) => $q->where('d.id', $request->filter_department))
            ->when($request->filled('filter_store'), fn($q) => $q->where('s.id', $request->filter_store))
            ->when($request->filled('filter_contract_type'), fn($q) => $q->where('contracts.contract_type', $request->filter_contract_type))
            ->where(function ($q) use ($request, $me) {
                // Tanpa pilihan: semua kecuali yang dibatalkan
                match ((string) $request->input('filter_view')) {
                    'active'      => $q->where('contracts.contract_status', 'Active'),
                    'ending_soon' => $q->where('contracts.contract_status', 'Active')->whereNotNull('contracts.end_date')
                        ->whereBetween('contracts.end_date', [today(), today()->addDays(self::ENDING_SOON_DAYS)]),
                    'expired'     => $q->where('contracts.contract_status', 'Expired'),
                    'terminated'  => $q->where('contracts.contract_status', 'Terminated'),
                    'pending'     => $q->where('contracts.contract_status', 'Pending'),
                    'draft'       => $q->where('contracts.contract_status', 'Draft'),
                    'cancelled'   => $q->where('contracts.contract_status', 'Cancelled'),
                    'my_turn'     => $me ? $this->myTurnScope($q, $me) : $q->whereRaw('0 = 1'),
                    default       => $q->where('contracts.contract_status', '!=', 'Cancelled'),
                };
            });

        $fmt = fn($d) => $d ? Carbon::parse($d)->locale('id')->translatedFormat('d M Y') : null;

        return DataTables::of($query)
            ->filterColumn('employee_name', fn($q, $k) => $q->where(fn($w) => $w->where('e.employee_name', 'like', "%$k%")->orWhere('e.employee_pengenal', 'like', "%$k%")))
            ->filterColumn('position_name', fn($q, $k) => $q->where('p.name', 'like', "%$k%"))
            ->orderColumn('end_date', 'contracts.end_date IS NULL, contracts.end_date $1')
            ->orderColumn('start_date', 'contracts.start_date $1')
            ->addColumn('start_label', fn($r) => $fmt($r->start_date))
            ->addColumn('end_label', fn($r) => $fmt($r->end_date))
            ->addColumn('days_left', fn($r) => $r->end_date && $r->contract_status === 'Active' ? (int) today()->diffInDays(Carbon::parse($r->end_date), false) : null)
            ->addColumn('status_label', function ($r) {
                if ($r->contract_status === 'Draft') return $r->returned_at ? 'Dikembalikan' : 'Draft';
                if ($r->contract_status === 'Pending') {
                    return 'Menunggu ' . (!$r->approver_2_at ? ($r->approver_2_name ?? 'approver 2') : ($r->approver_3_name ?? 'approver 3'));
                }
                return Contract::getContractStatusOptions()[$r->contract_status] ?? $r->contract_status;
            })
            ->addColumn('my_turn', fn($r) => $me && $r->contract_status === 'Pending' && (
                (!$r->approver_2_at && $r->approver_2 === $me->id) || ($r->approver_2_at && !$r->approver_3_at && $r->approver_3 === $me->id)))
            ->addColumn('has_file', fn($r) => (bool) $r->file_path)
            ->addColumn('show_url', fn($r) => route('showcontract', $r->id))
            ->make(true);
    }

    /** Riwayat perubahan kontrak (tanpa nominal gaji: dikecualikan dari log). */
    public function getActivities(Request $request)
    {
        $query = Activity::whereIn('log_name', ['Contract', 'contract'])
            ->where('subject_type', Contract::class)
            ->with(['causer.employee'])
            ->latest();

        return DataTables::of($query)
            ->addColumn('causer_name', fn($row) => $row->causer?->employee?->employee_name ?? 'Sistem')
            ->addColumn('when', fn($row) => $row->created_at?->timezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y, H:i'))
            ->filterColumn('description', fn($q, $k) => $q->where('description', 'like', "%$k%"))
            ->make(true);
    }

    /** Data form: karyawan (penempatan sekarang + gaji terakhir), daftar pilihan. */
    private function formData(?Contract $contract = null, ?SkLetter $fromSk = null, ?string $fromEmployeeId = null): array
    {
        // Gaji terakhir per karyawan (untuk isian awal; HR bisa mengubahnya)
        $latestSalary = EmployeeSalary::orderByDesc('effective_date')
            ->get(['employee_id', 'basic_salary', 'position_allowance', 'daily_rate'])
            ->unique('employee_id')->keyBy('employee_id');
        $activeContracts = Contract::where('contract_status', 'Active')->pluck('id', 'employee_id');

        $employees = Employee::whereIn('status', ['Active', 'Mutation', 'Pending', 'On Leave'])
            ->orderBy('employee_name')
            ->get(['id', 'employee_name', 'employee_pengenal', 'join_date', 'status_employee', 'company_id', 'position_id', 'department_id', 'store_id', 'grading_id', 'group_id'])
            ->mapWithKeys(fn($e) => [$e->id => [
                'name' => $e->employee_name, 'nip' => $e->employee_pengenal, 'type' => $e->status_employee,
                'join' => $e->join_date ? Carbon::parse($e->join_date)->format('Y-m-d') : null,
                'company' => $e->company_id, 'position' => $e->position_id, 'department' => $e->department_id, 'store' => $e->store_id,
                'grading' => $e->grading_id, 'group' => $e->group_id,
                'salary' => $latestSalary->has($e->id) ? [
                    'basic' => (int) $latestSalary[$e->id]->basic_salary, 'position' => (int) $latestSalary[$e->id]->position_allowance,
                    'daily' => (int) $latestSalary[$e->id]->daily_rate,
                ] : null,
                'active_contract' => $activeContracts[$e->id] ?? null,
            ]]);

        // Prefill dari SK (tombol "Buat kontrak" di SK yang sudah disetujui)
        $prefill = null;
        if ($fromSk && $fromEmployeeId) {
            $row = $fromSk->employees()->where('employees_tables.id', $fromEmployeeId)->first()?->pivot;
            if ($row) {
                $prefill = [
                    'employee_id' => $fromEmployeeId, 'sk_letter_id' => $fromSk->id, 'issuer_company_id' => $fromSk->company_id,
                    'start_date' => $fromSk->effective_date?->format('Y-m-d'), 'end_date' => $fromSk->inactive_date?->format('Y-m-d'),
                    'position_id' => $row->new_position_id ?? $row->position_id, 'department_id' => $row->new_department_id ?? $row->department_id,
                    'store_id' => $row->new_store_id ?? $row->previous_store_id, 'grading_id' => $row->new_grading_id ?? $row->grading_id,
                    'group_id' => $row->new_group_id ?? $row->group_id, 'contract_type' => $row->new_status_employee,
                    'basic_salary' => $row->basic_salary !== null ? (int) $row->basic_salary : null,
                    'positional_allowance' => $row->positional_allowance !== null ? (int) $row->positional_allowance : null,
                    'daily_rate' => $row->daily_rate !== null ? (int) $row->daily_rate : null,
                    // Approver ikut SK (bisa diganti)
                    'approver_2' => $fromSk->approver_2, 'approver_3' => $fromSk->approver_3,
                ];
            }
        }

        return [
            'contract'    => $contract,
            'prefill'     => $prefill,
            'fromSk'      => $fromSk,
            'employees'   => $employees,
            'types'       => Contract::getContractTypeOptions(),
            'companies'   => Company::orderBy('name')->pluck('name', 'id'),
            'positions'   => Position::orderBy('name')->pluck('name', 'id'),
            'departments' => Departments::orderBy('department_name')->pluck('department_name', 'id'),
            'stores'      => Stores::orderBy('name')->pluck('name', 'id'),
            'gradings'    => Grading::orderBy('level')->pluck('grading_name', 'id'),
            'groups'      => Groups::orderBy('group_name')->pluck('group_name', 'id'),
            'approvers'   => DB::table('employees_tables as e')->leftJoin('position_tables as p', 'p.id', '=', 'e.position_id')
                ->where('e.status', 'Active')->whereNull('e.deleted_at')->orderBy('e.employee_name')
                ->get(['e.id', 'e.employee_name', 'p.name as position']),
        ];
    }

    public function create(Request $request)
    {
        abort_unless($this->canManage(), 403, 'Hanya Admin / HeadHR yang bisa membuat kontrak.');

        $fromSk = $request->filled('sk') ? SkLetter::find($request->query('sk')) : null;
        if ($fromSk && !$fromSk->isFinal()) {
            return redirect()->route('SkLetters.show', $fromSk)->with('error', 'Kontrak hanya bisa dibuat dari SK yang sudah disetujui penuh.');
        }

        $data = $this->formData(null, $fromSk, $request->query('employee'));
        // "Kontrak baru" dari detail kontrak lama: karyawan sudah terpilih
        if (!$data['prefill'] && $request->filled('employee') && isset($data['employees'][$request->query('employee')])) {
            $data['prefill'] = ['employee_id' => $request->query('employee')];
        }

        return view('pages.contract.createcontract', $data + ['needPassword' => !$this->passwordConfirmed()]);
    }

    public function checkPassword(Request $request)
    {
        abort_unless($this->canManage(), 403);
        $request->validate(['password' => 'required|string']);
        if (Hash::check($request->password, auth()->user()->password)) {
            session(['contract_password_confirmed_at' => now()]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Password salah.'], 422);
    }

    /** Nominal diketik dengan titik ribuan (5.000.000) → angka. */
    private function normalizeMoney(Request $request): void
    {
        $clean = [];
        foreach (Contract::SALARY_FIELDS as $f) {
            if ($request->has($f)) {
                $v = trim((string) $request->input($f));
                $clean[$f] = $v === '' ? null : str_replace(['.', ' '], '', $v);
            }
        }
        $request->merge($clean);
    }

    private function rules(bool $creating): array
    {
        return array_filter([
            'employee_id'          => $creating ? ['required', Rule::exists('employees_tables', 'id')] : null,
            'sk_letter_id'         => $creating ? ['nullable', Rule::exists('sk_letters', 'id')] : null,
            'contract_type'        => ['required', Rule::in(array_keys(Contract::getContractTypeOptions()))],
            'issuer_company_id'    => ['required', Rule::exists('company_tables', 'id')],
            'position_id'          => ['nullable', Rule::exists('position_tables', 'id')],
            'department_id'        => ['nullable', Rule::exists('departments_tables', 'id')],
            'store_id'             => ['nullable', Rule::exists('stores_tables', 'id')],
            'grading_id'           => ['nullable', Rule::exists('grading', 'id')],
            'group_id'             => ['nullable', Rule::exists('groups_tables', 'id')],
            'start_date'           => ['required', 'date'],
            // PKWT & On Job Training wajib punya tanggal berakhir; PKWTT tidak
            'end_date'             => ['nullable', 'date', 'after_or_equal:start_date', 'required_if:contract_type,PKWT,On Job Training', 'prohibited_if:contract_type,PKWTT'],
            'basic_salary'         => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'positional_allowance' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'daily_rate'           => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'file_path'            => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'notes'                => ['nullable', 'string', 'max:2000'],
            'approver_2'           => ['required', 'different:approver_3', Rule::exists('employees_tables', 'id')],
            'approver_3'           => ['nullable', Rule::exists('employees_tables', 'id')],
        ]);
    }

    private const MESSAGES = [
        'end_date.required_if'    => 'Kontrak PKWT dan On Job Training wajib punya tanggal berakhir.',
        'end_date.prohibited_if'  => 'Kontrak PKWTT (karyawan tetap) tidak memakai tanggal berakhir.',
        'end_date.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        'file_path.mimes'         => 'Dokumen kontrak harus PDF.',
        'file_path.max'           => 'Dokumen kontrak maksimal 5 MB.',
        'approver_2.required'     => 'Pilih approver 2 (yang menyetujui setelah HR).',
        'approver_2.different'    => 'Approver 2 dan approver 3 harus orang yang berbeda.',
    ];

    private function storeFile(Request $request, Employee $employee): ?string
    {
        if (!$request->hasFile('file_path')) return null;
        // Dokumen kontrak di S3 privat; dibuka lewat route berlogin (file())
        return app(UploadStorage::class)->put($request->file('file_path'), 'contracts', 'contract-' . $employee->employee_name, ['pdf'], 's3', false, 'file_path');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canManage(), 403, 'Hanya Admin / HeadHR yang bisa membuat kontrak.');
        if (!$this->passwordConfirmed()) {
            return back()->withInput()->with('error', 'Konfirmasi password dulu (berlaku ' . self::PASSWORD_WINDOW_MINUTES . ' menit), lalu simpan lagi.');
        }

        $this->normalizeMoney($request);
        $data = $request->validate($this->rules(true), self::MESSAGES);
        $employee = Employee::findOrFail($data['employee_id']);

        try {
            $path = $this->storeFile($request, $employee);
            $contract = DB::transaction(function () use ($data, $employee, $path) {
                // Satu karyawan satu kontrak terbuka (Draft / menunggu persetujuan / berlaku)
                $open = Contract::where('employee_id', $employee->id)->whereIn('contract_status', Contract::OPEN_STATUSES)->lockForUpdate()->first();
                if ($open && $open->contract_status === 'Active') {
                    throw ValidationException::withMessages(['employee_id' => "{$employee->employee_name} masih punya kontrak berlaku (sampai " . ($open->end_date?->format('d/m/Y') ?? 'tanpa batas') . '). Akhiri kontrak itu dulu dari halaman detailnya.']);
                }
                if ($open) {
                    throw ValidationException::withMessages(['employee_id' => "{$employee->employee_name} sudah punya kontrak yang belum selesai diproses ({$open->status_label}). Lanjutkan atau batalkan kontrak itu."]);
                }
                return Contract::create(collect($data)->except('file_path')->all() + [
                    'company_id'      => $employee->company_id,
                    'file_path'       => $path,
                    'contract_status' => 'Draft',
                    'created_by'      => auth()->user()?->employee_id,
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Contract store gagal', ['employee_id' => $employee->id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Kontrak gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('showcontract', $contract->id)->with('success', "Kontrak {$employee->employee_name} disimpan sebagai Draft. Periksa lalu ajukan untuk persetujuan.");
    }

    public function show(string $id)
    {
        $contract = Contract::with(['employee', 'issuerCompany', 'position', 'department', 'store', 'grading', 'group', 'skLetter', 'approver1', 'approver2', 'approver3'])->findOrFail($id);
        $this->authorizeView($contract);
        $me = $this->me();

        return view('pages.contract.show', [
            'contract'     => $contract,
            'canManage'    => $this->canManage(),
            'canEdit'      => $this->canManage() && in_array($contract->contract_status, ['Draft', 'Active'], true),
            'canSubmit'    => $this->canManage() && $contract->contract_status === 'Draft',
            'canCancel'    => $this->canManage() && in_array($contract->contract_status, ['Draft', 'Pending'], true),
            'myTurn'       => $me && $contract->currentApproverId() === $me->id,
            'returnedBy'   => $contract->returned_by ? Employee::find($contract->returned_by)?->employee_name : null,
            'terminatedBy' => $contract->terminated_by ? Employee::find($contract->terminated_by)?->employee_name : null,
            'createdBy'    => $contract->created_by ? Employee::find($contract->created_by)?->employee_name : null,
            'history'      => $this->canViewAll()
                ? Contract::where('employee_id', $contract->employee_id)->where('id', '!=', $contract->id)->orderByDesc('start_date')->get(['id', 'contract_type', 'start_date', 'end_date', 'contract_status'])
                : collect(),
            'activities'   => Activity::where('subject_type', Contract::class)->where('subject_id', $contract->id)->latest()->limit(20)->get(['description', 'created_at']),
        ]);
    }

    /**
     * Draft: semua isi bisa diubah. Berlaku: hanya dokumen & catatan (isi kontrak yang sudah disetujui
     * tidak diubah; buat kontrak baru kalau syaratnya berubah). Menunggu persetujuan: tidak bisa diubah.
     */
    public function edit(string $id)
    {
        abort_unless($this->canManage(), 403, 'Hanya Admin / HeadHR yang bisa mengubah kontrak.');
        $contract = Contract::with('employee')->findOrFail($id);
        if (!in_array($contract->contract_status, ['Draft', 'Active'], true)) {
            return redirect()->route('showcontract', $id)->with('error', 'Kontrak yang menunggu persetujuan atau sudah selesai tidak bisa diubah.');
        }

        return view('pages.contract.edit', $this->formData($contract) + ['needPassword' => false, 'docOnly' => $contract->contract_status === 'Active']);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        abort_unless($this->canManage(), 403, 'Hanya Admin / HeadHR yang bisa mengubah kontrak.');
        $contract = Contract::with('employee')->findOrFail($id);
        if (!in_array($contract->contract_status, ['Draft', 'Active'], true)) {
            return redirect()->route('showcontract', $id)->with('error', 'Kontrak yang menunggu persetujuan atau sudah selesai tidak bisa diubah.');
        }

        if ($contract->contract_status === 'Active') {
            // Kontrak yang sudah disetujui: hanya dokumen bertanda tangan & catatan
            $data = $request->validate([
                'file_path' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
                'notes'     => ['nullable', 'string', 'max:2000'],
            ], self::MESSAGES);
        } else {
            $this->normalizeMoney($request);
            $data = $request->validate($this->rules(false), self::MESSAGES);
        }

        $path = $this->storeFile($request, $contract->employee);
        $old = $contract->file_path;
        $contract->update(collect($data)->except('file_path')->all() + ($path ? ['file_path' => $path] : []));
        if ($path && $old) {
            Storage::disk('s3')->delete($old);
        }

        return redirect()->route('showcontract', $id)->with('success', 'Perubahan kontrak disimpan.');
    }

    public function submit(string $id): RedirectResponse
    {
        abort_unless($this->canManage() && $this->me(), 403, 'Hanya Admin / HeadHR yang bisa mengajukan kontrak.');
        $contract = $this->service->submit(Contract::findOrFail($id), $this->me());

        return redirect()->route('showcontract', $id)->with('success', 'Kontrak diajukan. Menunggu persetujuan ' . ($contract->approver2?->employee_name ?? 'approver 2') . '.');
    }

    public function approve(string $id): RedirectResponse
    {
        abort_unless($this->me(), 403);
        $contract = $this->service->approve(Contract::findOrFail($id), $this->me());

        $message = in_array($contract->contract_status, ['Active', 'Expired'], true)
            ? 'Kontrak disetujui penuh dan ' . ($contract->contract_status === 'Active' ? 'sekarang berlaku.' : 'langsung berakhir karena tanggal berakhirnya sudah lewat.')
            : 'Disetujui. Diteruskan ke ' . ($contract->approver3?->employee_name ?? 'approver 3') . '.';

        return redirect()->route('showcontract', $id)->with('success', $message);
    }

    public function returnDraft(Request $request, string $id): RedirectResponse
    {
        abort_unless($this->me(), 403);
        $data = $request->validate(['note' => 'required|string|min:5|max:1000'], ['note.required' => 'Tulis alasan pengembalian.', 'note.min' => 'Alasan terlalu singkat.']);
        $this->service->returnToDraft(Contract::findOrFail($id), $this->me(), $data['note']);

        return redirect()->route('showcontract', $id)->with('success', 'Kontrak dikembalikan ke HR untuk diperbaiki.');
    }

    public function cancel(string $id): RedirectResponse
    {
        abort_unless($this->canManage(), 403, 'Hanya Admin / HeadHR yang bisa membatalkan kontrak.');
        $this->service->cancel(Contract::findOrFail($id));

        return redirect()->route('showcontract', $id)->with('success', 'Kontrak dibatalkan.');
    }

    /** Akhiri kontrak lebih awal (resign, PHK, diganti kontrak baru). */
    public function terminate(Request $request, string $id): RedirectResponse
    {
        abort_unless($this->canManage(), 403, 'Hanya Admin / HeadHR yang bisa mengakhiri kontrak.');
        $data = $request->validate([
            'terminated_at'     => ['required', 'date'],
            'terminated_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], ['terminated_reason.required' => 'Tulis alasan kontrak diakhiri.', 'terminated_reason.min' => 'Alasan terlalu singkat.']);

        DB::transaction(function () use ($id, $data) {
            $contract = Contract::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($contract->contract_status !== 'Active') {
                throw ValidationException::withMessages(['terminated_at' => 'Kontrak ini sudah tidak berlaku.']);
            }
            if (Carbon::parse($data['terminated_at'])->lt($contract->start_date)) {
                throw ValidationException::withMessages(['terminated_at' => 'Tanggal diakhiri tidak boleh sebelum tanggal mulai kontrak.']);
            }
            $contract->update($data + ['contract_status' => 'Terminated', 'terminated_by' => auth()->user()?->employee_id]);
        });

        return redirect()->route('showcontract', $id)->with('success', 'Kontrak diakhiri.');
    }

    /** Dokumen kontrak (PDF) dari S3, hanya untuk yang boleh membuka modul kontrak. */
    public function file(string $id)
    {
        $contract = Contract::with('employee')->findOrFail($id);
        $this->authorizeView($contract);
        abort_unless($contract->file_path && Storage::disk('s3')->exists($contract->file_path), 404, 'Dokumen kontrak belum diunggah.');

        return response(Storage::disk('s3')->get($contract->file_path), 200, [
            'Content-Type'           => 'application/pdf',
            'Content-Disposition'    => 'inline; filename="kontrak-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $contract->employee?->employee_name ?? 'karyawan') . '.pdf"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-store',
        ]);
    }
}
