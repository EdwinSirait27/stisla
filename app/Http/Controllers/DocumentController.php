<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Departments;
use App\Models\Documents;
use App\Models\Employee;
use App\Models\Grading;
use App\Models\Documenttypes;
use App\Models\Position;
use App\Models\Stores;
use App\Jobs\SendDocumentEmailJob;
use App\Services\SuratTugasService;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManageDocument')) {
            abort(403, 'Unauthorized');
        }
        $gradings      = Grading::pluck('grading_name')->unique()->sort()->values();
        $documentNames = Documenttypes::where('is_active', true)
            ->pluck('document_name')
            ->unique()
            ->sort()
            ->values();

        // Ringkasan untuk kartu di atas tabel (satu query agregat + satu query log kirim)
        $base = Documents::query()
            ->leftJoin('employees_tables as e', 'e.id', '=', 'documents.employee_id');
        $totals = (clone $base)->selectRaw("
                COUNT(*) AS total,
                SUM(documents.status = 'draft') AS draft,
                SUM(e.id IS NULL OR e.email IS NULL OR e.email = '') AS no_email,
                SUM(e.status IS NOT NULL AND e.status <> 'Active') AS inactive
            ")->first();
        $sentIds = Activity::where('log_name', 'document')->where('event', 'sent')
            ->where('subject_type', Documents::class)->distinct()->pluck('subject_id');
        $byType = Documents::query()
            ->join('company_document_configs as c', 'c.id', '=', 'documents.company_document_config_id')
            ->join('document_types as t', 't.id', '=', 'c.document_type_id')
            ->selectRaw('t.document_name, t.nickname, COUNT(*) AS c')
            ->groupBy('t.document_name', 't.nickname')
            ->orderByDesc('c')
            ->get();

        $stats = [
            'total'    => (int) $totals->total,
            'draft'    => (int) $totals->draft,
            'no_email' => (int) $totals->no_email,
            'inactive' => (int) $totals->inactive,
            'sent'     => $sentIds->count(),
            'by_type'  => $byType,
        ];

        return view('pages.document.index', compact('gradings', 'documentNames', 'stats'));
    }

    public function create()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403, 'Unauthorized');
        }

        return view('pages.document.create', [
            'employees'   => Employee::where('status', 'Active')->orderBy('employee_name')->get(),
            'departments' => Departments::orderBy('department_name')->get(),
            'stores'      => Stores::orderBy('name')->get(),
            'positions'   => Position::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, SuratTugasService $suratTugasService)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'document_type'    => 'required|in:ST',
            'employee_id'      => 'required|uuid|exists:employees_tables,id',
            'department_id'    => 'nullable|uuid|exists:departments_tables,id',
            'store_id'         => 'nullable|array',
            'store_id.*'       => 'uuid|exists:stores_tables,id',
            'position_id'      => 'nullable|uuid|exists:position_tables,id',
            'issued_date'      => 'required|date',
            'expired_date'     => 'required|date|after:issued_date',
        ], [
            'document_type.in'     => 'Tipe dokumen ini belum bisa dibuat manual dari halaman ini.',
            'employee_id.required' => 'Pilih karyawan.',
        ]);

        $targets = array_filter([
            'department' => $validated['department_id'] ?? null,
            'store'      => $validated['store_id'] ?? null,
            'position'   => $validated['position_id'] ?? null,
        ]);

        if (empty($targets)) {
            return back()
                ->withErrors(['assignment' => 'Pilih minimal satu tujuan pemindahan (departemen, store, atau posisi).'])
                ->withInput();
        }

        try {
            $suratTugasService->createSuratTugas($validated['employee_id'], [
                'issued_date'  => $validated['issued_date'],
                'expired_date' => $validated['expired_date'],
                'targets'      => $targets,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('document.index')
            ->with('success', 'Dokumen berhasil dibuat.');
    }

    public function getDocuments()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403, 'Unauthorized');
        }

        $lastSent = Activity::select('created_at')
            ->whereColumn('subject_id', 'documents.id')
            ->where('subject_type', Documents::class)
            ->where('log_name', 'document')
            ->where('event', 'sent')
            ->latest()
            ->limit(1);
        $lastFailed = Activity::select('created_at')
            ->whereColumn('subject_id', 'documents.id')
            ->where('subject_type', Documents::class)
            ->where('log_name', 'document')
            ->where('event', 'send_failed')
            ->latest()
            ->limit(1);

        $query = Documents::with([
                'companydocumentconfigs.documenttypes',
                'employee:id,employee_name,employee_pengenal,grading_id,email,status',
                'employee.grading:id,grading_name',
            ])->select([
                'documents.id',
                'company_document_config_id',
                'employee_id',
                'issued_by',
                'issued_date',
                'expired_date',
                'document_number',
                'status',
            ])
            ->addSelect(['last_sent_at' => $lastSent, 'last_failed_at' => $lastFailed]);

        $query->when(request()->filled('filter_grading'), function ($q) {
            $q->whereHas('employee.grading', function ($gq) {
                $gq->where('grading_name', request('filter_grading'));
            });
        });

        $query->when(request()->filled('filter_document_name'), function ($q) {
            $q->whereHas('companydocumentconfigs.documenttypes', function ($dq) {
                $dq->where('document_name', request('filter_document_name'));
            });
        });

        $query->when(request()->filled('filter_status'), function ($q) {
            $q->where('status', request('filter_status'));
        });

        // Pengiriman: never | sent | failed | no_email
        $query->when(request('filter_send'), function ($q, $v) use ($lastSent, $lastFailed) {
            match ($v) {
                'sent'     => $q->whereExists((clone $lastSent)->getQuery()),
                'never'    => $q->whereNotExists((clone $lastSent)->getQuery())
                                ->whereHas('employee', fn($e) => $e->whereNotNull('email')->where('email', '!=', '')),
                'failed'   => $q->whereExists((clone $lastFailed)->getQuery()),
                'no_email' => $q->where(fn($w) => $w->whereDoesntHave('employee')
                                ->orWhereHas('employee', fn($e) => $e->whereNull('email')->orWhere('email', ''))),
                default    => null,
            };
        });

        $fmt = fn($v) => $v ? Carbon::parse($v)->locale('id')->translatedFormat('d M Y') : null;

        return DataTables::of($query)
            ->addColumn('checkbox', function ($document) {
                $reason = $this->unsendableReason($document);
                $label  = 'Pilih dokumen ' . e($document->document_number);

                return '<input type="checkbox" class="doc-checkbox" value="' . e($document->id) . '" aria-label="' . $label . '"'
                    . ($reason ? ' disabled title="' . e($reason) . '"' : '') . '>';
            })
            ->addColumn('employee_name', function ($document) {
                $emp = $document->employee;
                if (!$emp) {
                    return '<span class="pr-cell-name">-</span><span class="pr-cell-sub">Karyawan tidak ditemukan</span>';
                }
                $sub = collect([$emp->employee_pengenal, $emp->grading->grading_name ?? null])->filter()->implode(', ');
                $inactive = $emp->status !== 'Active'
                    ? ' <span class="status-badge is-draft">' . e($emp->status ?? 'Nonaktif') . '</span>' : '';

                return '<span class="pr-cell-name">' . e($emp->employee_name) . $inactive . '</span>'
                    . '<span class="pr-cell-sub">' . e($sub ?: '-') . '</span>';
            })
            ->addColumn('grading_name', function ($document) {
                return $document->employee->grading->grading_name ?? '-';
            })
            ->addColumn('document_name', function ($document) {
                $type = $document->companydocumentconfigs->documenttypes ?? null;

                return '<span class="pr-cell-name">' . e($type->document_name ?? '-') . '</span>'
                    . '<span class="pr-cell-sub">' . e($document->document_number ?? '-') . '</span>';
            })
            ->addColumn('issued', function ($document) use ($fmt) {
                $html = '<span class="pr-cell-name">' . e($fmt($document->issued_date) ?? '-') . '</span>';
                if ($document->expired_date) {
                    $html .= '<span class="pr-cell-sub">berlaku sampai ' . e($fmt($document->expired_date)) . '</span>';
                }
                return $html;
            })
            ->addColumn('delivery', function ($document) use ($fmt) {
                $email = $document->employee->email ?? null;
                if (!$email) {
                    return '<span class="status-badge is-rejected">Tidak ada email</span>'
                        . '<span class="pr-cell-sub">Isi email karyawan dulu</span>';
                }
                $sent   = $document->last_sent_at;
                $failed = $document->last_failed_at;
                if ($failed && (!$sent || $failed > $sent)) {
                    return '<span class="status-badge is-rejected">Gagal terkirim</span>'
                        . '<span class="pr-cell-sub">' . e($fmt($failed)) . ', ' . e($email) . '</span>';
                }
                if ($sent) {
                    return '<span class="status-badge is-approved">Terkirim</span>'
                        . '<span class="pr-cell-sub">' . e(Carbon::parse($sent)->locale('id')->translatedFormat('d M Y H:i')) . '</span>';
                }
                return '<span class="status-badge is-draft">Belum dikirim</span>'
                    . '<span class="pr-cell-sub">' . e($email) . '</span>';
            })
            ->editColumn('status', function ($document) {
                $badges = [
                    'draft'   => ['is-draft', 'Draft'],
                    'issued'  => ['is-approved', 'Terbit'],
                    'revoked' => ['is-rejected', 'Dicabut'],
                    'expired' => ['is-pending', 'Kedaluwarsa'],
                ];
                [$cls, $label] = $badges[$document->status] ?? ['is-draft', ucfirst((string) $document->status)];

                return '<span class="status-badge ' . $cls . '">' . e($label) . '</span>';
            })
            ->addColumn('action', function ($document) {
                $reason   = $this->unsendableReason($document);
                $isDraft  = $document->status === 'draft';
                $nickname = $document->companydocumentconfigs->documenttypes->nickname ?? null;
                $number   = e($document->document_number);

                $html = '<div class="action-wrap">';
                $html .= $isDraft
                    ? '<button type="button" class="act-btn" disabled title="Draft belum bisa diunduh"><i class="fas fa-download" aria-hidden="true"></i><span class="sr-only">Unduh</span></button>'
                    : '<a href="' . route('documents.download', $document->id) . '" target="_blank" rel="noopener" class="act-btn" title="Unduh PDF ' . $number . '"><i class="fas fa-download" aria-hidden="true"></i><span class="sr-only">Unduh</span></a>';
                $html .= $reason
                    ? '<button type="button" class="act-btn" disabled title="' . e($reason) . '"><i class="fas fa-paper-plane" aria-hidden="true"></i><span class="sr-only">Kirim</span></button>'
                    : '<button type="button" class="act-btn btn-send-document" data-url="' . route('documents.send', $document->id) . '" data-number="' . $number . '" data-email="' . e($document->employee->email) . '" title="Kirim ke email karyawan"><i class="fas fa-paper-plane" aria-hidden="true"></i><span class="sr-only">Kirim</span></button>';
                if ($nickname === 'ST' && $document->status === SuratTugasService::ACTIVE_STATUS) {
                    $html .= '<a href="' . route('documents.st.edit', $document->id) . '" class="act-btn act-warning" title="Edit surat tugas"><i class="fas fa-pen" aria-hidden="true"></i><span class="sr-only">Edit</span></a>';
                }
                return $html . '</div>';
            })
            ->filterColumn('employee_name', function ($query, $keyword) {
                $query->whereHas('employee', function ($q) use ($keyword) {
                    $q->where('employee_name', 'like', "%{$keyword}%")
                        ->orWhere('employee_pengenal', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('grading_name', function ($query, $keyword) {
                $query->whereHas('employee.grading', function ($q) use ($keyword) {
                    $q->where('grading_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('document_name', function ($query, $keyword) {
                $query->where(function ($w) use ($keyword) {
                    $w->where('document_number', 'like', "%{$keyword}%")
                        ->orWhereHas('companydocumentconfigs.documenttypes', fn($q) => $q->where('document_name', 'like', "%{$keyword}%"));
                });
            })
            ->orderColumn('employee_name', function ($query, $order) {
                $query->orderBy(
                    Employee::select('employee_name')->whereColumn('employees_tables.id', 'documents.employee_id')->limit(1),
                    $order
                );
            })
            ->orderColumn('document_name', function ($query, $order) {
                $query->orderBy(
                    \App\Models\Companydocumentconfigs::select('document_types.document_name')
                        ->join('document_types', 'document_types.id', '=', 'company_document_configs.document_type_id')
                        ->whereColumn('company_document_configs.id', 'documents.company_document_config_id')
                        ->limit(1),
                    $order
                );
            })
            ->orderColumn('issued', 'issued_date $1')
            ->orderColumn('delivery', 'last_sent_at $1')
            ->rawColumns(['checkbox', 'employee_name', 'document_name', 'issued', 'delivery', 'status', 'action'])
            ->make(true);
    }

    /** Alasan dokumen tidak bisa dikirim lewat email, null kalau bisa. */
    private function unsendableReason(Documents $document): ?string
    {
        if ($document->status === 'draft') {
            return 'Dokumen masih draft, belum bisa dikirim';
        }
        if ($document->status === 'revoked') {
            return 'Dokumen sudah dicabut';
        }
        if (!$document->employee) {
            return 'Karyawan tidak ditemukan';
        }
        if (empty($document->employee->email)) {
            return 'Karyawan belum punya email';
        }
        return null;
    }

    public function getDocumentActivities(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403, 'Unauthorized');
        }

        $query = Activity::where('log_name', 'document')
            ->with(['causer.employee'])
            ->latest();

        $events = [
            'created'     => ['is-open', 'Dibuat'],
            'updated'     => ['is-pending', 'Diubah'],
            'deleted'     => ['is-rejected', 'Dihapus'],
            'sent'        => ['is-approved', 'Dikirim'],
            'send_failed' => ['is-rejected', 'Gagal kirim'],
        ];

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('event_badge', function ($row) use ($events) {
                [$cls, $label] = $events[$row->event] ?? ['is-draft', ucfirst((string) ($row->event ?: 'lainnya'))];
                return '<span class="status-badge ' . $cls . '">' . e($label) . '</span>';
            })
            ->addColumn('description', function ($row) {
                return e($row->description ?? '-');
            })
            ->addColumn('causer', function ($row) {
                return $row->causer->employee->employee_name ?? ($row->causer->name ?? 'Sistem');
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at->locale('id')->translatedFormat('d M Y H:i');
            })
            ->filter(function ($instance) use ($request) {
                if ($request->has('search') && $request->get('search')['value'] != '') {
                    $search = $request->get('search')['value'];

                    $instance->where(function ($q) use ($search) {
                        $q->where('description', 'like', "%{$search}%")
                            ->orWhereHas('causer.employee', function ($q2) use ($search) {
                                $q2->where('employee_name', 'like', "%{$search}%");
                            });
                    });
                }
            })
            ->rawColumns(['event_badge', 'description'])
            ->make(true);
    }

    public function sendDocument(string $documentId)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403, 'Unauthorized');
        }

        $document = Documents::with('employee:id,email,employee_name')->findOrFail($documentId);

        if ($reason = $this->unsendableReason($document)) {
            return response()->json(['message' => $reason . '.'], 422);
        }

        SendDocumentEmailJob::dispatch($document->id, $user->id);

        return response()->json([
            'message' => "Dokumen {$document->document_number} masuk antrian kirim ke {$document->employee->email}. Status di tabel berubah jadi Terkirim setelah email benar-benar keluar.",
        ]);
    }

    public function bulkSendDocument(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'document_ids'   => 'required|array|min:1|max:500',
            'document_ids.*' => 'required|string|exists:documents,id',
        ]);

        $documents = Documents::with('employee:id,email')->whereIn('id', $validated['document_ids'])->get();
        $queued = 0;
        $skipped = [];

        foreach ($documents as $document) {
            if ($reason = $this->unsendableReason($document)) {
                $skipped[$reason] = ($skipped[$reason] ?? 0) + 1;
                continue;
            }
            SendDocumentEmailJob::dispatch($document->id, $user->id);
            $queued++;
        }

        $message = "{$queued} dokumen masuk antrian kirim.";
        if ($skipped) {
            $message .= ' Dilewati: ' . collect($skipped)->map(fn($n, $r) => "{$n} ({$r})")->implode(', ') . '.';
        }

        return response()->json(['message' => $message, 'queued' => $queued, 'skipped' => array_sum($skipped)]);
    }

    public function downloadDocument(string $documentId)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403);
        }

        $document = Documents::with([
            'employee',
            'employee.position',
            'issued.position',
            'companydocumentconfigs.company',
            'companydocumentconfigs.documenttypes',
            'assignments',
        ])->findOrFail($documentId);

        if (
            !$document->companydocumentconfigs ||
            !$document->companydocumentconfigs->documenttypes
        ) {
            abort(404);
        }

        $viewName = $document
            ->companydocumentconfigs
            ->documenttypes
            ->view_name;
        $allowedViews = [
            'documents.types.SPK',
            'documents.types.SPPRP',
            'documents.types.PAK',
            'documents.types.ST',
        ];
        if (!in_array($viewName, $allowedViews)) {
            abort(403);
        }
        $signatureData = null;
        if ($document->issued && $document->issued->signature) {
            $path = 'employees-signatures-photos/' . basename($document->issued->signature);
            if (Storage::disk('s3')->exists($path)) {
                $signatureData = 'data:image/png;base64,' . base64_encode(
                    Storage::disk('s3')->get($path)
                );
            }
        }
        $pdf = Pdf::loadView($viewName, [
            'document'      => $document,
            'employee'      => $document->employee,
            'issued'        => $document->issued,
            'config'        => $document->companydocumentconfigs,
            'company'       => $document->companydocumentconfigs->company,
            'signatureData' => $signatureData,
        ])->setPaper('a4');

        $password = Carbon::parse(
            $document->employee->date_of_birth
        )->format('Ymd');
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->getCanvas();

        if (method_exists($canvas, 'get_cpdf')) {
            $cpdf = $canvas->get_cpdf();
            $cpdf->setEncryption($password, $password);
        }

        $filename = str_replace('/', '-', $document->document_number) . '.pdf';
        return $pdf->download($filename);
    }
    public function generateOfferingLetter(string $employeeId)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    if (!$user || !$user->hasPermissionTo('ManageDocument')) {
        abort(403);
    }

    $employee = \App\Models\Employee::with([
        'primaryStore',
        'primaryDepartment',
        'primaryPosition',
        'grading',
        'salary' => fn($q) => $q->latest('effective_date'),
    ])->findOrFail($employeeId);

    // Pastikan status_employee DW atau On Job Training
    if (!in_array($employee->status_employee, ['DW', 'On Job Training'])) {
        abort(403, 'Employee tidak eligible untuk Offering Letter');
    }

    return view('pages.document.offering-letter-form', [
        'employee' => $employee,
    ]);
}

public function storeOfferingLetter(Request $request, string $employeeId)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    if (!$user || !$user->hasPermissionTo('ManageDocument')) {
        abort(403);
    }

    $employee = \App\Models\Employee::with([
        'primaryStore',
        'primaryDepartment',
        'primaryPosition',
        'grading',
    ])->findOrFail($employeeId);

    if (!in_array($employee->status_employee, ['DW', 'On Job Training'])) {
        abort(403);
    }

    // Validasi sesuai status_employee
    $rules = [
        'point_of_hire' => 'required|string|max:255',
    ];

    if ($employee->status_employee === 'On Job Training') {
        $rules['basic_salary']        = 'required|numeric|min:0';
        $rules['position_allowance']  = 'required|numeric|min:0';
    } else {
        // DW
        $rules['daily_rate'] = 'required|numeric|min:0';
    }

    $validated = $request->validate($rules);

    DB::transaction(function () use ($employee, $validated, $user) {
        // 1. Simpan ke EmployeeSalary
        \App\Models\EmployeeSalary::create([
            'employee_id'        => $employee->id,
            'basic_salary'       => $validated['basic_salary'] ?? 0,
            'position_allowance' => $validated['position_allowance'] ?? 0,
            'daily_rate'         => $validated['daily_rate'] ?? 0,
            'effective_date'     => $employee->join_date,
            'created_by'         => $user->employee_id,
        ]);

        // 2. Simpan point_of_hire ke Employee
        $employee->update([
            'point_of_hire' => $validated['point_of_hire'],
        ]);

        // 3. Resolve config berdasarkan status_employee
        $nickname = $employee->status_employee === 'DW' ? 'OL-DW' : 'OL-OJT';

        $config = \App\Models\Companydocumentconfigs::with(['documenttypes'])
            ->where('company_id', $employee->company_id)
            ->whereHas('documenttypes', fn($q) => $q->where('nickname', $nickname))
            ->where('is_active', true)
            ->firstOrFail();

        // 4. Cek apakah sudah ada offering letter
        $existing = Documents::where('company_document_config_id', $config->id)
            ->where('employee_id', $employee->id)
            ->exists();

        if ($existing) {
            abort(422, 'Offering Letter sudah pernah di-generate untuk employee ini');
        }

        // 5. Buat dokumen
        $headHR = \App\Models\User::role('HeadHR')
            ->whereHas('Employee', fn($q) => $q->where('status', 'Active'))
            ->first();

        Documents::create([
            'company_document_config_id' => $config->id,
            'employee_id'                => $employee->id,
            'issued_by'                  => $headHR?->employee_id ?? $user->employee_id,
            'issued_date'                => $employee->join_date,
            'status'                     => 'draft',
        ]);
    });

    return redirect()
        ->route('documents.index')
        ->with('success', 'Offering Letter berhasil di-generate');
}

public function downloadOfferingLetter(string $documentId)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    if (!$user || !$user->hasPermissionTo('ManageDocument')) {
        abort(403);
    }

    $document = Documents::with([
        'employee.primaryStore',
        'employee.primaryDepartment',
        'employee.primaryPosition',
        'employee.grading',
        'employee.salaries' => fn($q) => $q->latest('effective_date'),
        'issued.position',
        'companydocumentconfigs.company',
        'companydocumentconfigs.documenttypes',
    ])->findOrFail($documentId);

    $viewName = $document->companydocumentconfigs->documenttypes->view_name;

    $allowedViews = [
        'documents.types.OLOJT',
        'documents.types.OLDW',
    ];

    if (!in_array($viewName, $allowedViews)) {
        abort(403);
    }

    $signatureData = null;
    if ($document->issued && $document->issued->signature) {
        $path = 'employees-signatures-photos/' . basename($document->issued->signature);
        if (Storage::disk('s3')->exists($path)) {
            $signatureData = 'data:image/png;base64,' . base64_encode(
                Storage::disk('s3')->get($path)
            );
        }
    }

    $salary = \App\Models\EmployeeSalary::where('employee_id', $document->employee_id)
    ->where('effective_date', $document->employee->join_date)
    ->latest('effective_date')
    ->first();

    $pdf = Pdf::loadView($viewName, [
        'document'      => $document,
        'employee'      => $document->employee,
        'issued'        => $document->issued,
        'config'        => $document->companydocumentconfigs,
        'company'       => $document->companydocumentconfigs->company,
        'salary'        => $salary,
        'signatureData' => $signatureData,
    ])->setPaper('a4');

    $password = Carbon::parse($document->employee->date_of_birth)->format('Ymd');
    $domPdf   = $pdf->getDomPDF();
    $canvas   = $domPdf->getCanvas();

    if (method_exists($canvas, 'get_cpdf')) {
        $cpdf = $canvas->get_cpdf();
        $cpdf->setEncryption($password, $password);
    }

    $filename = str_replace('/', '-', $document->document_number) . '.pdf';
    return $pdf->download($filename);
}
}
