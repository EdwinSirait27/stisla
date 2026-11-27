<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\Grading;
use App\Models\Groups;
use App\Models\Position;
use App\Models\SkLetter;
use App\Models\Sktype;
use App\Models\Stores;
use App\Services\SkLetterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

/**
 * Surat Keputusan (SK). Alur & penerapan ada di SkLetterService.
 *
 * Akses:
 * - ManageSkLetters : daftar semua SK, buat Draft.
 * - ManageSkLetters + SubmitSkLetters : ubah Draft, ajukan untuk persetujuan, batalkan.
 * - Approver 2 / 3 yang dipilih di SK : membuka SK itu, menyetujui atau mengembalikan saat gilirannya,
 *   walau tanpa ManageSkLetters (SK memuat gaji, jadi selain itu tertutup).
 */
class SkLetterController extends Controller
{
    public function __construct(protected SkLetterService $service)
    {
        $this->middleware('auth');
    }

    private function me(): ?Employee
    {
        return auth()->user()?->employee;
    }

    private function canManage(): bool
    {
        return (bool) auth()->user()?->can('ManageSkLetters');
    }

    /** Mengubah Draft, mengajukan, membatalkan: permission SubmitSkLetters (bukan role). */
    private function canSubmitSk(): bool
    {
        return (bool) auth()->user()?->can('SubmitSkLetters');
    }

    /** Approver 2 / 3 dari SK ini (bukan HR pengaju). */
    private function isApproverOf(SkLetter $sk): bool
    {
        $me = $this->me();
        return $me && in_array($me->id, array_filter([$sk->approver_2, $sk->approver_3]), true);
    }

    private function authorizeView(SkLetter $sk): void
    {
        abort_unless($this->canManage() || $this->isApproverOf($sk), 403, 'Anda tidak terlibat dalam SK ini.');
    }

    public function index()
    {
        $canManage = $this->canManage();
        $me = $this->me();
        // Tanpa ManageSkLetters hanya approver yang bisa membuka daftar (SK yang menyangkut dirinya)
        abort_unless($canManage || ($me && SkLetter::where('approver_2', $me->id)->orWhere('approver_3', $me->id)->exists()), 403);

        $counts = SkLetter::select('status', DB::raw('COUNT(*) AS c'))->groupBy('status')->pluck('c', 'status');
        $myTurn = $me ? SkLetter::where(fn($q) => $q->where('status', 'Approved HR')->where('approver_2', $me->id))
            ->orWhere(fn($q) => $q->where('status', 'Approved Director')->where('approver_3', $me->id))->count() : 0;

        return view('pages.SkLetters.index', [
            'canManage' => $canManage,
            'canCreate' => $canManage,
            'sktypes'   => Sktype::orderBy('sk_name')->pluck('sk_name', 'id'),
            'companies' => Company::orderBy('name')->pluck('name', 'id'),
            'stats'     => [
                'draft'    => (int) ($counts['Draft'] ?? 0),
                'waiting'  => (int) (($counts['Approved HR'] ?? 0) + ($counts['Approved Director'] ?? 0)),
                'approved' => (int) ($counts[SkLetter::FINAL_STATUS] ?? 0),
                'my_turn'  => $myTurn,
            ],
        ]);
    }

    public function getSkLetters(Request $request)
    {
        $canManage = $this->canManage();
        $me = $this->me();

        $query = SkLetter::query()
            ->leftJoin('sk_type as st', 'st.id', '=', 'sk_letters.sk_type_id')
            ->leftJoin('company_tables as c', 'c.id', '=', 'sk_letters.company_id')
            ->leftJoin('employees_tables as a2', 'a2.id', '=', 'sk_letters.approver_2')
            ->leftJoin('employees_tables as a3', 'a3.id', '=', 'sk_letters.approver_3')
            ->select([
                'sk_letters.id', 'sk_letters.sk_number', 'sk_letters.title', 'sk_letters.status', 'sk_letters.effective_date',
                'sk_letters.inactive_date', 'sk_letters.approver_2', 'sk_letters.approver_3', 'sk_letters.returned_at',
                'sk_letters.created_at', 'st.sk_name', 'st.nickname as sk_nickname', 'c.name as company_name',
                'a2.employee_name as approver_2_name', 'a3.employee_name as approver_3_name',
            ])
            ->selectSub(fn($q) => $q->from('sk_letter_employees')->whereColumn('sk_letter_id', 'sk_letters.id')->selectRaw('COUNT(*)'), 'employee_count')
            ->selectSub(fn($q) => $q->from('sk_letter_employees as se')->join('employees_tables as e', 'e.id', '=', 'se.employee_id')
                ->whereColumn('se.sk_letter_id', 'sk_letters.id')->selectRaw("GROUP_CONCAT(e.employee_name ORDER BY e.employee_name SEPARATOR ', ')"), 'employee_names');

        if (!$canManage) {
            abort_unless($me, 403);
            $query->where(fn($q) => $q->where('sk_letters.approver_2', $me->id)->orWhere('sk_letters.approver_3', $me->id));
        }

        $query->when($request->filled('filter_company'), fn($q) => $q->where('sk_letters.company_id', $request->filter_company))
            ->when($request->filled('filter_sk_type'), fn($q) => $q->where('sk_letters.sk_type_id', $request->filter_sk_type))
            ->when($request->filled('filter_status'), function ($q) use ($request) {
                $request->filter_status === 'waiting'
                    ? $q->whereIn('sk_letters.status', ['Approved HR', 'Approved Director'])
                    : $q->where('sk_letters.status', $request->filter_status);
            })
            ->when($request->filter_status !== 'Cancelled' && !$request->boolean('with_cancelled'), fn($q) => $q->where('sk_letters.status', '!=', 'Cancelled'))
            ->when($request->filled('filter_effective_date_start'), fn($q) => $q->whereDate('sk_letters.effective_date', '>=', $request->filter_effective_date_start))
            ->when($request->filled('filter_effective_date_end'), fn($q) => $q->whereDate('sk_letters.effective_date', '<=', $request->filter_effective_date_end))
            ->when($request->boolean('my_turn') && $me, fn($q) => $q->where(fn($w) => $w
                ->where(fn($x) => $x->where('sk_letters.status', 'Approved HR')->where('sk_letters.approver_2', $me->id))
                ->orWhere(fn($x) => $x->where('sk_letters.status', 'Approved Director')->where('sk_letters.approver_3', $me->id))));

        $fmt = fn($d) => $d ? Carbon::parse($d)->locale('id')->translatedFormat('d M Y') : null;

        return DataTables::of($query)
            ->filterColumn('sk_number', fn($q, $k) => $q->where(fn($w) => $w->where('sk_letters.sk_number', 'like', "%$k%")
                ->orWhere('sk_letters.title', 'like', "%$k%")
                ->orWhereExists(fn($e) => $e->from('sk_letter_employees as se2')->join('employees_tables as e2', 'e2.id', '=', 'se2.employee_id')
                    ->whereColumn('se2.sk_letter_id', 'sk_letters.id')->where('e2.employee_name', 'like', "%$k%"))))
            ->orderColumn('effective_date', 'sk_letters.effective_date $1')
            ->addColumn('status_label', function ($row) {
                return match ($row->status) {
                    'Draft'             => $row->returned_at ? 'Dikembalikan' : 'Draft',
                    'Approved HR'       => 'Menunggu ' . ($row->approver_2_name ?? 'approver 2'),
                    'Approved Director' => 'Menunggu ' . ($row->approver_3_name ?? 'approver 3'),
                    SkLetter::FINAL_STATUS => 'Disetujui',
                    'Cancelled'         => 'Dibatalkan',
                    default             => $row->status,
                };
            })
            ->addColumn('my_turn', fn($row) => $me && (
                ($row->status === 'Approved HR' && $row->approver_2 === $me->id)
                || ($row->status === 'Approved Director' && $row->approver_3 === $me->id)))
            ->addColumn('effective_label', fn($row) => $fmt($row->effective_date))
            ->addColumn('inactive_label', fn($row) => $fmt($row->inactive_date))
            ->addColumn('show_url', fn($row) => route('SkLetters.show', $row->id))
            ->addColumn('pdf_url', fn($row) => route('SkLetters.pdf', $row->id))
            ->make(true);
    }

    /** Data pilihan untuk form buat & edit SK. */
    private function formData(): array
    {
        $people = DB::table('employees_tables as e')
            ->leftJoin('position_tables as p', 'p.id', '=', 'e.position_id')
            ->where('e.status', 'Active')->whereNull('e.deleted_at')
            ->orderBy('e.employee_name')
            ->get(['e.id', 'e.employee_name', 'e.employee_pengenal', 'p.name as position']);

        return [
            'sktypes'     => Sktype::orderBy('sk_name')->get(['id', 'sk_name', 'nickname', 'categories', 'affects_salary', 'affects_position', 'affects_status', 'generates_contract']),
            'companies'   => Company::orderBy('name')->pluck('name', 'id'),
            'employees'   => Employee::query()
                ->whereIn('status', ['Active', 'Pending', 'Mutation', 'On Leave'])
                ->with(['grading:id,grading_name', 'group:id,group_name'])
                ->orderBy('employee_name')
                ->get(['id', 'employee_name', 'employee_pengenal', 'status_employee', 'position_id', 'department_id', 'store_id', 'grading_id', 'group_id']),
            'approvers'   => $people,
            'positions'   => Position::orderBy('name')->get(['id', 'name']),
            'departments' => Departments::orderBy('department_name')->get(['id', 'department_name']),
            'stores'      => Stores::orderBy('name')->get(['id', 'name']),
            'gradings'    => Grading::orderBy('level')->get(['id', 'grading_name']),
            'groups'      => Groups::orderBy('group_name')->get(['id', 'group_name']),
            'empTypes'    => ['PKWT', 'PKWTT', 'On Job Training', 'DW'],
        ];
    }

    public function create()
    {
        abort_unless($this->canManage(), 403);
        return view('pages.SkLetters.create', $this->formData() + ['skLetter' => null]);
    }

    /** Nominal diketik dengan titik ribuan (5.000.000) → angka. */
    private function normalizeMoney(Request $request): void
    {
        $request->merge([
            'employees' => collect($request->employees)->map(function ($employee) {
                foreach (['basic_salary', 'positional_allowance', 'daily_rate'] as $field) {
                    $employee[$field] = isset($employee[$field]) && $employee[$field] !== ''
                        ? str_replace(['.', ' '], '', $employee[$field])
                        : null;
                }
                return $employee;
            })->values()->toArray(),
        ]);
    }

    private function validated(Request $request): array
    {
        $this->normalizeMoney($request);

        return $request->validate([
            'sk_type_id'      => 'required|exists:sk_type,id',
            'title'           => 'required|string|max:255',
            'company_id'      => 'required|exists:company_tables,id',
            'approver_2'      => 'required|different:approver_3|exists:employees_tables,id',
            'approver_3'      => 'nullable|exists:employees_tables,id',
            'effective_date'  => 'required|date',
            'inactive_date'   => 'nullable|date|after:effective_date',
            'location'        => 'nullable|string|max:255',
            'menetapkan_text' => 'nullable|string|max:5000',
            'notes'           => 'nullable|string|max:2000',

            'employees'                        => 'required|array|min:1|max:100',
            'employees.*.employee_id'          => 'required|distinct|exists:employees_tables,id',
            'employees.*.new_position_id'      => 'nullable|exists:position_tables,id',
            'employees.*.new_department_id'    => 'nullable|exists:departments_tables,id',
            'employees.*.new_store_id'         => 'nullable|exists:stores_tables,id',
            'employees.*.new_grading_id'       => 'nullable|exists:grading,id',
            'employees.*.new_group_id'         => 'nullable|exists:groups_tables,id',
            'employees.*.new_status_employee'  => 'nullable|in:PKWT,PKWTT,On Job Training,DW',
            'employees.*.basic_salary'         => 'nullable|numeric|min:0|max:9999999999',
            'employees.*.positional_allowance' => 'nullable|numeric|min:0|max:9999999999',
            'employees.*.daily_rate'           => 'nullable|numeric|min:0|max:9999999999',
            'employees.*.notes'                => 'nullable|string|max:1000',

            'menimbang'   => 'nullable|array|max:30',
            'menimbang.*' => 'nullable|string|max:1000',
            'mengingat'   => 'nullable|array|max:30',
            'mengingat.*' => 'nullable|string|max:1000',
            'keputusan'   => 'nullable|array|max:30',
            'keputusan.*' => 'nullable|string|max:2000',
        ], [
            'employees.required'               => 'Tambahkan minimal satu karyawan.',
            'employees.*.employee_id.distinct' => 'Karyawan yang sama dipilih lebih dari sekali.',
            'approver_2.required'              => 'Pilih approver 2 (yang menyetujui setelah HR).',
            'approver_2.different'             => 'Approver 2 dan approver 3 harus orang yang berbeda.',
            'inactive_date.after'              => 'Tanggal berakhir harus setelah tanggal berlaku.',
            'title.required'                   => 'Judul SK wajib diisi.',
            'effective_date.required'          => 'Tanggal berlaku wajib diisi.',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canManage(), 403);
        // Isi request (termasuk gaji) sengaja tidak ditulis ke log: datanya sensitif
        $validated = $this->validated($request);

        try {
            $skLetter = $this->service->store($validated);
        } catch (\Throwable $e) {
            Log::error('SK Letter store gagal', ['user_id' => auth()->id(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            return back()->with('error', 'SK gagal dibuat. Silakan coba lagi.')->withInput();
        }

        return redirect()->route('SkLetters.show', $skLetter)
            ->with('success', "SK {$skLetter->sk_number} dibuat sebagai Draft. Periksa lalu ajukan untuk persetujuan.");
    }

    public function show(SkLetter $skletter)
    {
        $this->authorizeView($skletter);

        $skletter->load([
            'sktype', 'company', 'approver1', 'approver2', 'approver3', 'menimbang', 'mengingat', 'keputusan', 'contracts',
            'employees' => fn($q) => $q->orderBy('employee_name'),
        ]);
        $names = fn(string $table, string $col, string $label) => DB::table($table)->pluck($label, $col);

        $me = $this->me();
        $step = $skletter->currentStep();

        return view('pages.SkLetters.show', [
            'skletter'    => $skletter,
            'canManage'   => $this->canManage(),
            'canEdit'     => $this->canManage() && $this->canSubmitSk() && $skletter->status === 'Draft',
            'canSubmit'   => $this->canManage() && $this->canSubmitSk() && $skletter->status === 'Draft',
            'canCancel'   => $this->canManage() && $this->canSubmitSk() && !in_array($skletter->status, [SkLetter::FINAL_STATUS, 'Cancelled'], true),
            'myTurn'      => $me && $step && $skletter->{$step[0]} === $me->id,
            'returnedBy'  => $skletter->returned_by ? Employee::find($skletter->returned_by)?->employee_name : null,
            'positions'   => $names('position_tables', 'id', 'name'),
            'departments' => $names('departments_tables', 'id', 'department_name'),
            'stores'      => $names('stores_tables', 'id', 'name'),
            'gradings'    => $names('grading', 'id', 'grading_name'),
            'groups'      => $names('groups_tables', 'id', 'group_name'),
            'activities'  => \Spatie\Activitylog\Models\Activity::where('subject_type', SkLetter::class)->where('subject_id', $skletter->id)
                ->latest()->limit(30)->get(['description', 'created_at']),
        ]);
    }

    /** Permission SubmitSkLetters, dan hanya selama SK masih Draft. */
    private function canEdit(SkLetter $skletter): bool
    {
        return $skletter->status === 'Draft' && $this->canManage() && $this->canSubmitSk();
    }

    // Nama parameter harus sama dengan route ({skletter}) supaya route model binding jalan
    public function edit(SkLetter $skletter)
    {
        if (!$this->canEdit($skletter)) {
            return redirect()->route('SkLetters.show', $skletter)
                ->with('error', 'Mengubah SK butuh permission SubmitSkLetters, dan hanya selama SK masih Draft.');
        }

        $skletter->load(['employees', 'menimbang', 'mengingat', 'keputusan']);

        return view('pages.SkLetters.edit', ['skLetter' => $skletter] + $this->formData());
    }

    public function update(Request $request, SkLetter $skletter): RedirectResponse
    {
        if (!$this->canEdit($skletter)) {
            return redirect()->route('SkLetters.show', $skletter)
                ->with('error', 'Mengubah SK butuh permission SubmitSkLetters, dan hanya selama SK masih Draft.');
        }

        $validated = $this->validated($request);

        try {
            $this->service->update($skletter, $validated);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SK Letter update gagal', ['sk_letter_id' => $skletter->id, 'message' => $e->getMessage(), 'line' => $e->getLine()]);
            return back()->with('error', 'SK gagal disimpan. Silakan coba lagi.')->withInput();
        }

        return redirect()->route('SkLetters.show', $skletter)->with('success', 'Perubahan SK disimpan.');
    }

    public function submit(SkLetter $skletter): RedirectResponse
    {
        abort_unless($this->canManage() && $this->canSubmitSk() && $this->me(), 403, 'Mengajukan SK butuh permission SubmitSkLetters.');
        $this->service->submit($skletter, $this->me());

        return redirect()->route('SkLetters.show', $skletter)
            ->with('success', 'SK diajukan. Menunggu persetujuan ' . ($skletter->fresh()->approver2?->employee_name ?? 'approver 2') . '.');
    }

    public function approve(SkLetter $skletter): RedirectResponse
    {
        abort_unless($this->me(), 403);
        $sk = $this->service->approve($skletter, $this->me());

        $message = $sk->isFinal()
            ? 'SK disetujui penuh. ' . ($sk->effective_date->isFuture()
                ? 'Perubahan jabatan dan penempatan diterapkan otomatis pada ' . $sk->effective_date->locale('id')->translatedFormat('d F Y') . '.'
                : 'Perubahan sudah diterapkan ke data karyawan.')
            : 'Disetujui. Diteruskan ke ' . ($sk->approver3?->employee_name ?? 'approver 3') . '.';

        return redirect()->route('SkLetters.show', $skletter)->with('success', $message);
    }

    public function returnDraft(Request $request, SkLetter $skletter): RedirectResponse
    {
        abort_unless($this->me(), 403);
        $data = $request->validate(['note' => 'required|string|min:5|max:1000'], [
            'note.required' => 'Tulis alasan pengembalian.',
            'note.min'      => 'Alasan terlalu singkat.',
        ]);
        $this->service->returnToDraft($skletter, $this->me(), $data['note']);

        return redirect()->route('SkLetters.show', $skletter)->with('success', 'SK dikembalikan ke HR untuk diperbaiki.');
    }

    public function cancel(SkLetter $skletter): RedirectResponse
    {
        abort_unless($this->canManage() && $this->canSubmitSk(), 403, 'Membatalkan SK butuh permission SubmitSkLetters.');
        $this->service->cancel($skletter);

        return redirect()->route('SkLetters.show', $skletter)->with('success', 'SK dibatalkan.');
    }

    public function viewPdf(SkLetter $skLetter)
    {
        $this->authorizeView($skLetter);

        $skLetter->load(['sktype', 'company', 'approver1', 'approver2', 'approver3', 'menimbang', 'mengingat', 'keputusan', 'employees']);

        // Tanda tangan dari S3 disisipkan sebagai data URI (dompdf tidak membaca S3 / URL berlogin)
        $image = function (?string $path): ?string {
            if (!$path) return null;
            try {
                if (!Storage::disk('s3')->exists($path)) return null;
                return 'data:image/png;base64,' . base64_encode(Storage::disk('s3')->get($path));
            } catch (\Throwable $e) {
                return null;
            }
        };
        $signatures = [
            1 => $skLetter->approver_1_at ? $image($skLetter->approver_1_signature) : null,
            2 => $skLetter->approver_2_at ? $image($skLetter->approver_2_signature) : null,
            3 => $skLetter->approver_3_at ? $image($skLetter->approver_3_signature) : null,
        ];
        $titleOf = fn(?Employee $e) => $e ? ($e->primaryPosition()->value('position_tables.name') ?? Position::find($e->position_id)?->name) : null;
        $titles = [1 => $titleOf($skLetter->approver1), 2 => $titleOf($skLetter->approver2), 3 => $titleOf($skLetter->approver3)];
        $names = [
            'positions'   => DB::table('position_tables')->pluck('name', 'id'),
            'departments' => DB::table('departments_tables')->pluck('department_name', 'id'),
            'stores'      => DB::table('stores_tables')->pluck('name', 'id'),
        ];

        $pdf = Pdf::loadView('pages.SkLetters.pdf', compact('skLetter', 'signatures', 'titles', 'names'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SK-' . str_replace('/', '-', $skLetter->sk_number) . '.pdf');
    }
}
