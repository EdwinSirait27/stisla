<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Stores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeSalaryImport;
use App\Exports\EmployeeSalaryExport;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Grading;
use Yajra\DataTables\Facades\DataTables;

class EmployeeSalaryController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.view')) {
            abort(403, 'Unauthorized');
        }
        $stats = [
            'total' => EmployeeSalary::distinct('employee_id')->count(),
            'pkwt'  => EmployeeSalary::whereHas('employee', fn($q) => $q->where('status_employee', 'PKWT'))->distinct('employee_id')->count(),
            'ojt'   => EmployeeSalary::whereHas('employee', fn($q) => $q->where('status_employee', 'On Job Training'))->distinct('employee_id')->count(),
            'dw'    => EmployeeSalary::whereHas('employee', fn($q) => $q->where('status_employee', 'DW'))->distinct('employee_id')->count(),
            // Gaji dengan effective_date setelah hari ini (belum dipakai payroll periode berjalan)
            'scheduled' => EmployeeSalary::whereDate('effective_date', '>', now()->toDateString())->count(),
        ];

        // Karyawan aktif yang belum punya data gaji sama sekali → payroll-nya tidak bisa di-generate
        $missingAll = $this->missingSalaryEmployees();
        $stats['missing'] = $missingAll->count();
        $missingEmployees = $missingAll->take(8);

        $stores = Stores::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $gradings = Grading::orderBy('grading_name')->get();
        $departments = Departments::orderBy('department_name')->get();

        return view('pages.EmployeeSalary.index', compact('companies', 'stats', 'stores', 'gradings', 'departments', 'missingEmployees', 'missingAll'));
    }

    /** Karyawan aktif (punya NIP) yang belum punya satu pun data gaji. */
    private function missingSalaryEmployees()
    {
        return Employee::where('status', 'Active')
            ->whereNotNull('employee_pengenal')
            ->whereNotIn('id', EmployeeSalary::select('employee_id'))
            ->with([
                'store' => fn($q) => $q->wherePivot('is_primary', true),
                'department' => fn($q) => $q->wherePivot('is_primary', true),
                'position' => fn($q) => $q->wherePivot('is_primary', true),
            ])
            ->orderBy('employee_name')
            ->get(['id', 'employee_name', 'employee_pengenal', 'status_employee', 'status', 'join_date']);
    }

    /** Excel karyawan aktif tanpa gaji, format sama dengan template import. */
    public function exportMissing()
    {
        $user = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.export')) {
            abort(403, 'Unauthorized');
        }

        return Excel::download(
            new \App\Exports\MissingSalaryExport($this->missingSalaryEmployees()),
            'karyawan_tanpa_gaji_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    // ── DataTable ──
    public function getEmployeeSalaries(Request $request)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.view')) {
            abort(403, 'Unauthorized');
        }

        $query = EmployeeSalary::with([
            'employee:id,employee_name,employee_pengenal,status_employee,grading_id,company_id,store_id,department_id,status',
            'employee.grading:id,grading_name',
            'employee.company:id,name',
            'employee.department:id,department_name',
            'employee.store:id,name',
            'employee.store' => fn($q) => $q->wherePivot('is_primary', true),
            'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
            'employee.position' => fn($q) => $q->wherePivot('is_primary', true),
        ])->select('employee_salaries.*');

        // Gaji yang berlaku hari ini = effective_date terakhir yang <= hari ini, per karyawan
        $today = now()->toDateString();
        $currentSub = "employee_salaries.effective_date = (
            SELECT MAX(s2.effective_date) FROM employee_salaries s2
            WHERE s2.employee_id = employee_salaries.employee_id AND s2.effective_date <= ?
        )";
        $query->selectRaw("({$currentSub}) AS is_current", [$today]);

        match ($request->input('scope', 'current')) {
            'scheduled' => $query->whereDate('employee_salaries.effective_date', '>', $today),
            'all'       => null,
            default     => $query->whereRaw($currentSub, [$today]),
        };

        if ($request->filled('store_name')) {
            $query->whereHas(
                'employee.store',
                fn($q) =>
                $q->where('name', $request->store_name)
            );
        }
        if ($request->filled('grading_name')) {
            $query->whereHas(
                'employee.grading',
                fn($q) =>
                $q->where('grading_name', $request->grading_name)
            );
        }
       
        if ($request->filled('company_name')) {
            $query->whereHas(
                'employee.company',
                fn($q) => $q->where('name', $request->company_name)
            );
        }
        if ($request->filled('department_name')) {
            $query->whereHas(
                'employee.department',
                fn($q) =>
                $q->where('department_name', $request->department_name)
            );
        }

        if ($request->filled('status_employee')) {
            $query->whereHas(
                'employee',
                fn($q) =>
                $q->where('status_employee', $request->status_employee)
            );
        }
        if ($request->filled('status')) {
            $query->whereHas(
                'employee',
                fn($q) =>
                $q->where('status', $request->status)
            );
        }
        if ($request->filled('effective_date')) {
            $query->where('effective_date', $request->effective_date);
        }

        return DataTables::of($query)
            ->addColumn('employee_name', fn($row) => $row->employee->employee_name ?? '-')
            ->addColumn('employee_pengenal', fn($row) => $row->employee->employee_pengenal ?? '-')
            ->addColumn('status_employee', fn($row) => $row->employee->status_employee ?? '-')
            ->addColumn('status', fn($row) => $row->employee->status ?? '-')
            ->addColumn('grading_name', fn($row) => $row->employee->grading->grading_name ?? '-')
            ->addColumn('company_name', fn($row) => $row->employee->company->name ?? '-')
            ->addColumn('basic_salary_fmt', fn($row) => number_format($row->basic_salary, 0, ',', '.'))
            ->addColumn('position_allowance_fmt', fn($row) => number_format($row->position_allowance, 0, ',', '.'))
            ->addColumn('daily_rate_fmt', fn($row) => number_format($row->daily_rate, 0, ',', '.'))
            ->addColumn('meal_allowance_fmt', fn($row) => number_format($row->meal_allowance, 0, ',', '.'))
            ->addColumn('house_allowance_fmt', fn($row) => number_format($row->house_allowance, 0, ',', '.'))
            ->addColumn('transport_allowance_fmt', fn($row) => number_format($row->transport_allowance, 0, ',', '.'))
            ->addColumn('bpjs_ketenagakerjaan_fmt', fn($row) => number_format($row->bpjs_ketenagakerjaan, 0, ',', '.'))
            ->addColumn('bpjs_kesehatan_fmt', fn($row) => number_format($row->bpjs_kesehatan, 0, ',', '.'))
            ->addColumn('store_name', fn($row) => $row->employee->store->first()?->name ?? '-')
            ->addColumn('department_name', fn($row) => $row->employee->department->first()?->department_name ?? '-')
            ->addColumn('position_name', fn($row) => $row->employee->position->first()?->name ?? '-')
            ->filterColumn('employee_name', fn($q, $k) => $q->whereHas('employee', fn($q2) => $q2->where('employee_name', 'like', "%$k%")))
            ->filterColumn('employee_pengenal', fn($q, $k) => $q->whereHas('employee', fn($q2) => $q2->where('employee_pengenal', 'like', "%$k%")))
            ->filterColumn('status_employee', fn($q, $k) => $q->whereHas('employee', fn($q2) => $q2->where('status_employee', 'like', "%$k%")))
            ->filterColumn('status', fn($q, $k) => $q->whereHas('employee', fn($q2) => $q2->where('status', 'like', "%$k%")))
            ->filterColumn('store_name', fn($q, $k) => $q->whereHas('employee.store', fn($q2) => $q2->where('name', 'like', "%$k%")))
            ->filterColumn('grading_name', fn($q, $k) => $q->whereHas('employee.grading', fn($q2) => $q2->where('grading_name', 'like', "%$k%")))
            ->filterColumn('company_name', fn($q, $k) => $q->whereHas('employee.company', fn($q2) => $q2->where('name', 'like', "%$k%")))
            ->filterColumn('department_name', fn($q, $k) => $q->whereHas('employee.department', fn($q2) => $q2->where('department_name', 'like', "%$k%")))
            ->filterColumn('position_name', fn($q, $k) => $q->whereHas('employee.position', fn($q2) => $q2->where('name', 'like', "%$k%")))
            ->orderColumn('employee_name', function ($q, $order) {
                $q->join('employees_tables as e', 'e.id', '=', 'employee_salaries.employee_id')
                    ->orderBy('e.employee_name', $order);
            })
            ->orderColumn('status_employee', function ($q, $order) {
                $q->join('employees_tables as e', 'e.id', '=', 'employee_salaries.employee_id')
                    ->orderBy('e.status_employee', $order);
            })
            ->orderColumn('status', function ($q, $order) {
                $q->join('employees_tables as e', 'e.id', '=', 'employee_salaries.employee_id')
                    ->orderBy('e.status', $order);
            })
            ->orderColumn('grading_name', function ($q, $order) {
                $q->join('employees_tables as e_g', 'e_g.id', '=', 'employee_salaries.employee_id')
                    ->join('grading as g', 'g.id', '=', 'e_g.grading_id')
                    ->orderBy('g.grading_name', $order);
            })
            ->orderColumn('company_name', function ($q, $order) {
                $q->join('employees_tables as e_c', 'e_c.id', '=', 'employee_salaries.employee_id')
                    ->join('company_tables as c', 'c.id', '=', 'e_c.company_id')
                    ->orderBy('c.name', $order);
            })
            ->orderColumn(
                'employee_pengenal',
                fn($q, $order) =>
                $q->whereHas('employee', fn($q2) => $q2->orderBy('employee_pengenal', $order))
            )

            ->orderColumn('employee_pengenal', function ($q, $order) {
                $q->join('employees_tables as e', 'e.id', '=', 'employee_salaries.employee_id')
                    ->orderBy('e.employee_pengenal', $order);
            })
            ->orderColumn('store_name', function ($q, $order) {
                $q->join('employees_tables as e2', 'e2.id', '=', 'employee_salaries.employee_id')
                    ->join('employee_stores as es', 'es.employee_id', '=', 'e2.id')
                    ->join('stores_tables as s', 's.id', '=', 'es.store_id')
                    ->where('es.is_primary', true)
                    ->orderBy('s.name', $order);
            })
            ->orderColumn('position_name', function ($q, $order) {
                $q->join('employees_tables as e2', 'e2.id', '=', 'employee_salaries.employee_id')
                    ->join('employee_positions as es', 'es.employee_id', '=', 'e2.id')
                    ->join('position_tables as p', 'p.id', '=', 'es.position_id')
                    ->where('es.is_primary', true)
                    ->orderBy('p.name', $order);
            })
            ->orderColumn('department_name', function ($q, $order) {
                $q->join('employees_tables as e3', 'e3.id', '=', 'employee_salaries.employee_id')
                    ->join('employee_departments as ed', 'ed.employee_id', '=', 'e3.id')
                    ->join('departments_tables as d', 'd.id', '=', 'ed.department_id')
                    ->where('ed.is_primary', true)
                    ->orderBy('d.department_name', $order);
            })
            ->addColumn('effective_date_fmt', fn($row) => $row->effective_date?->locale('id')->translatedFormat('d M Y') ?? '-')
            ->addColumn('salary_state', fn($row) => match (true) {
                $row->effective_date?->toDateString() > now()->toDateString() => 'scheduled',
                (bool) $row->is_current => 'current',
                default => 'history',
            })
            ->addColumn('action', fn($row) => $user->can('employee.salary.update') ? '
                <a href="' . route('employeesalary.edit', $row->id) . '" class="act-btn" title="Edit gaji" aria-label="Edit gaji">
                    <i class="fas fa-edit" aria-hidden="true"></i>
                </a>
            ' : '')

            ->rawColumns(['action'])
            ->make(true);
    }
    public function getActivitySalary(Request $request)
    {
        $user = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.view')) {
            abort(403, 'Unauthorized');
        }

        $query = Activity::with('causer')
            ->where('log_name', 'employee_salary')
            ->latest();


        return DataTables::of($query)
            ->addColumn('causer_name', fn($row) => $row->causer?->employee->employee_name ?? '-')
            ->addColumn('event_badge', fn($row) => match ($row->event) {
                'created' => '<span class="status-badge is-created">Dibuat</span>',
                'updated' => '<span class="status-badge is-updated">Diubah</span>',
                'deleted' => '<span class="status-badge is-deleted">Dihapus</span>',
                default   => '<span class="status-badge">' . e($row->event) . '</span>',
            })
            ->addColumn(
                'employee_name',
                fn($row) =>
                $row->properties['attributes']['employee_name'] ?? '-'
            )
            ->addColumn(
                'effective_date',
                function ($row) {
                    $raw = $row->properties['attributes']['effective_date'] ?? null;
                    if (!$raw || $raw === '-') {
                        return '-';
                    }
                    // EmployeeSalary menyimpan log dalam format d/m/Y; Carbon::parse akan membacanya sebagai m/d/Y
                    $date = preg_match('#^\d{2}/\d{2}/\d{4}$#', $raw)
                        ? \Carbon\Carbon::createFromFormat('d/m/Y', $raw)
                        : \Carbon\Carbon::parse($raw)->setTimezone(config('app.timezone'));
                    return $date->locale('id')->translatedFormat('d M Y');
                }
            )
            // ── New values ──
            ->addColumn(
                'basic_salary_new',
                fn($row) =>
                isset($row->properties['attributes']['basic_salary'])
                    ? number_format($row->properties['attributes']['basic_salary'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'position_allowance_new',
                fn($row) =>
                isset($row->properties['attributes']['position_allowance'])
                    ? number_format($row->properties['attributes']['position_allowance'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'daily_rate_new',
                fn($row) =>
                isset($row->properties['attributes']['daily_rate'])
                    ? number_format($row->properties['attributes']['daily_rate'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'meal_allowance_new',
                fn($row) =>
                isset($row->properties['attributes']['meal_allowance'])
                    ? number_format($row->properties['attributes']['meal_allowance'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'transport_allowance_new',
                fn($row) =>
                isset($row->properties['attributes']['transport_allowance'])
                    ? number_format($row->properties['attributes']['transport_allowance'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'house_allowance_new',
                fn($row) =>
                isset($row->properties['attributes']['house_allowance'])
                    ? number_format($row->properties['attributes']['house_allowance'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'bpjs_ketenagakerjaan_new',
                fn($row) =>
                isset($row->properties['attributes']['bpjs_ketenagakerjaan'])
                    ? number_format($row->properties['attributes']['bpjs_ketenagakerjaan'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'bpjs_kesehatan_new',
                fn($row) =>
                isset($row->properties['attributes']['bpjs_kesehatan'])
                    ? number_format($row->properties['attributes']['bpjs_kesehatan'], 0, ',', '.')
                    : '-'
            )
            // ── Old values (hanya saat updated) ──
            ->addColumn(
                'basic_salary_old',
                fn($row) =>
                isset($row->properties['old']['basic_salary'])
                    ? number_format($row->properties['old']['basic_salary'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'position_allowance_old',
                fn($row) =>
                isset($row->properties['old']['position_allowance'])
                    ? number_format($row->properties['old']['position_allowance'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'daily_rate_old',
                fn($row) =>
                isset($row->properties['old']['daily_rate'])
                    ? number_format($row->properties['old']['daily_rate'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'meal_allowance_old',
                fn($row) =>
                isset($row->properties['old']['meal_allowance'])
                    ? number_format($row->properties['old']['meal_allowance'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'transport_allowance_old',
                fn($row) =>
                isset($row->properties['old']['transport_allowance'])
                    ? number_format($row->properties['old']['transport_allowance'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'house_allowance_old',
                fn($row) =>
                isset($row->properties['old']['house_allowance'])
                    ? number_format($row->properties['old']['house_allowance'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'bpjs_ketenagakerjaan_old',
                fn($row) =>
                isset($row->properties['old']['bpjs_ketenagakerjaan'])
                    ? number_format($row->properties['old']['bpjs_ketenagakerjaan'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'bpjs_kesehatan_old',
                fn($row) =>
                isset($row->properties['old']['bpjs_kesehatan'])
                    ? number_format($row->properties['old']['bpjs_kesehatan'], 0, ',', '.')
                    : '-'
            )
            ->addColumn(
                'changed_at',
                fn($row) =>
                $row->created_at->locale('id')->translatedFormat('d M Y, H:i')
            )
            ->filterColumn(
                'employee_name',
                fn($q, $k) =>
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(properties, '$.attributes.employee_name')) like ?", ["%$k%"])
            )
            ->filterColumn(
                'causer_name',
                fn($q, $k) =>
                $q->whereHas(
                    'causer',
                    fn($q2) =>
                    $q2->whereHas(
                        'employee',
                        fn($q3) =>
                        $q3->where('employee_name', 'like', "%$k%")
                    )
                )
            )

            ->rawColumns(['event_badge'])
            ->make(true);
    }
    public function export(Request $request)
    {
        $user = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.export')) {
            abort(403, 'Unauthorized');
        }

        return Excel::download(
            // Argumen bernama: urutan constructor (store, company, grading, department) beda dengan urutan filter
            new EmployeeSalaryExport(
                effectiveDate: $request->effective_date,
                status: $request->status,
                storeName: $request->store_name,
                companyName: $request->company_name,
                gradingName: $request->grading_name,
                departmentName: $request->department_name,
            ),
            'employee_salary_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
    // ── Create ──
    public function create()
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.create')) {
            abort(403, 'Unauthorized');
        }
        $employees = Employee::where(function ($query) {
            // Status normal tetap muncul
            $query->whereIn('status', ['Active', 'Mutation', 'Pending', 'On Leave']);
        })
            ->orWhere(function ($query) {
                // Status Resign hanya muncul jika end_date + 2 bulan >= hari ini
                $query->where('status', 'Resign')
                    ->whereNotNull('end_date')
                    ->whereDate('end_date', '>=', now()->subMonths(2));
            })
            ->whereNotNull('employee_pengenal')
            ->orderBy('employee_name')
            ->get();

        // Riwayat gaji per karyawan, untuk info "gaji saat ini" dan peringatan tanggal yang sama
        $history = $this->salaryHistory($employees->pluck('id')->all());

        return view('pages.EmployeeSalary.create', compact('employees', 'history'));
    }

    /** @return array<string, array<int, array>> employee_id => daftar gaji (terbaru dulu) */
    private function salaryHistory(array $employeeIds): array
    {
        return EmployeeSalary::whereIn('employee_id', $employeeIds)
            ->orderByDesc('effective_date')
            ->get()
            ->groupBy('employee_id')
            ->map(fn($rows) => $rows->map(fn($r) => [
                'id'        => $r->id,
                'date'      => $r->effective_date?->format('Y-m-d'),
                'basic'     => (float) $r->basic_salary,
                'position'  => (float) $r->position_allowance,
                'daily'     => (float) $r->daily_rate,
                'meal'      => (float) $r->meal_allowance,
                'house'     => (float) $r->house_allowance,
                'transport' => (float) $r->transport_allowance,
                'bpjs_kes'  => (float) $r->bpjs_kesehatan,
                'bpjs_tk'   => (float) $r->bpjs_ketenagakerjaan,
            ])->values()->all())
            ->all();
    }
    private function normalizeCurrency(Request $request): void
    {
        $fields = [
            'basic_salary',
            'position_allowance',
            'daily_rate',
            'meal_allowance',
            'house_allowance',
            'transport_allowance',
            'bpjs_kesehatan',
            'bpjs_ketenagakerjaan',
        ];

        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $request->merge([
                    $field => str_replace(
                        ',',
                        '.',
                        str_replace('.', '', $request->$field)
                    )
                ]);
            }
        }
    }
    public function store(Request $request)
    {
        $user = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.create')) {
            abort(403, 'Unauthorized');
        }
        $this->normalizeCurrency($request);
        $request->validate([
            'employee_id'        => 'required|exists:employees_tables,id',
            'basic_salary'       => 'nullable|numeric|min:0',
            'position_allowance' => 'nullable|numeric|min:0',
            'daily_rate'         => 'nullable|numeric|min:0',
            'meal_allowance'         => 'nullable|numeric|min:0',
            'house_allowance'         => 'nullable|numeric|min:0',
            'transport_allowance'         => 'nullable|numeric|min:0',
            'bpjs_ketenagakerjaan'         => 'nullable|numeric|min:0',
            'bpjs_kesehatan'         => 'nullable|numeric|min:0',
            'effective_date'     => 'required|date',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $status   = strtoupper($employee->status_employee);

        try {
            // Cek apakah sudah ada data untuk employee + effective_date yang sama
            $existing = EmployeeSalary::where('employee_id', $request->employee_id)
                ->where('effective_date', $request->effective_date)
                ->first();

            $data = [
                'basic_salary'       => $status === 'DW' ? 0 : $request->basic_salary ?? 0,
                'position_allowance' => $status === 'DW' ? 0 : $request->position_allowance ?? 0,
                'daily_rate'         => $status === 'DW' ? $request->daily_rate ?? 0 : 0,
                'meal_allowance'       => $request->meal_allowance ?? 0,       // ← tambah
                'house_allowance'      => $request->house_allowance ?? 0,      // ← tambah
                'transport_allowance'  => $request->transport_allowance ?? 0,  // ← tambah
                'bpjs_kesehatan'       => $request->bpjs_kesehatan ?? 0,       // ← tambah
                'bpjs_ketenagakerjaan' => $request->bpjs_ketenagakerjaan ?? 0, // ← tambah
                'created_by'         => $user->employee_id,
            ];

            if ($existing) {
                // Update → trigger 'updated' event → Spatie log
                $existing->update($data);
                $message = 'Employee Salary updated successfully.';
            } else {
                // Create → trigger 'created' event → Spatie log
                EmployeeSalary::create(array_merge($data, [
                    'employee_id'    => $request->employee_id,
                    'effective_date' => $request->effective_date,
                ]));
                $message = 'Employee Salary saved successfully.';
            }

            return redirect()
                ->route('employeesalary.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('EmployeeSalary store error: ' . $e->getMessage());
            return back()
                ->with('error', 'Failed to save salary: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ── Edit ──
    public function edit(string $id)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.update')) {
            abort(403, 'Unauthorized');
        }
        $salary  = EmployeeSalary::with('employee')->findOrFail($id);
        $history = $this->salaryHistory([$salary->employee_id])[$salary->employee_id] ?? [];

        return view('pages.EmployeeSalary.edit', compact('salary', 'history'));
    }

    // ── Update ──
    public function update(Request $request, string $id)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.update')) {
            abort(403, 'Unauthorized');
        }
        $salary   = EmployeeSalary::findOrFail($id);
        $employee = $salary->employee;
        $status   = strtoupper($employee->status_employee);
        $this->normalizeCurrency($request);

        $request->validate([
            'basic_salary'       => 'nullable|numeric|min:0',
            'position_allowance' => 'nullable|numeric|min:0',
            'daily_rate'         => 'nullable|numeric|min:0',
            'meal_allowance'       => 'nullable|numeric|min:0',
            'house_allowance'      => 'nullable|numeric|min:0',
            'transport_allowance'  => 'nullable|numeric|min:0',
            'bpjs_kesehatan'       => 'nullable|numeric|min:0',
            'bpjs_ketenagakerjaan' => 'nullable|numeric|min:0',
            'effective_date'     => [
                'required',
                'date',
                // Satu karyawan tidak boleh punya dua gaji di tanggal berlaku yang sama:
                // payroll memakai gaji terakhir yang berlaku, dua baris sama tanggal jadi ambigu.
                function ($attr, $value, $fail) use ($salary) {
                    $clash = EmployeeSalary::where('employee_id', $salary->employee_id)
                        ->whereDate('effective_date', $value)
                        ->where('id', '!=', $salary->id)
                        ->exists();
                    if ($clash) {
                        $fail('Karyawan ini sudah punya data gaji lain dengan tanggal berlaku yang sama. Ubah data itu, atau pilih tanggal lain.');
                    }
                },
            ],
        ]);
        try {
            $salary->update([
                'basic_salary'       => $status === 'DW' ? 0 : $request->basic_salary ?? 0,
                'position_allowance' => $status === 'DW' ? 0 : $request->position_allowance ?? 0,
                'daily_rate'         => $status === 'DW' ? $request->daily_rate ?? 0 : 0,
                'meal_allowance'       => $request->meal_allowance ?? 0,
                'house_allowance'      => $request->house_allowance ?? 0,
                'transport_allowance'  => $request->transport_allowance ?? 0,
                'bpjs_kesehatan'       => $request->bpjs_kesehatan ?? 0,
                'bpjs_ketenagakerjaan' => $request->bpjs_ketenagakerjaan ?? 0,
                'effective_date'     => $request->effective_date,
            ]);

            return redirect()
                ->route('employeesalary.index')
                ->with('success', 'Employee Salary updated successfully.');
        } catch (\Exception $e) {
            Log::error('EmployeeSalary update error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update salary.');
        }
    }
    // ── Import Excel ──
    public function import(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.import')) {
            abort(403, 'Unauthorized');
        }
        $request->validate([
            'file'           => 'required|mimes:xlsx,xls|max:10240',
            'effective_date' => 'required|date',
        ]);


        try {
            $createdBy = Auth::user()->employee->id ?? null;

            $import = new EmployeeSalaryImport($request->effective_date, $createdBy);
            Excel::import($import, $request->file('file'));

            if (!empty($import->skipped)) {
                return back()
                    ->with('success', 'Import selesai.')
                    ->with('skipped', $import->skipped);
            }

            return back()->with('success', 'Import successful.');
        } catch (\Exception $e) {
            Log::error('EmployeeSalary import error: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
    public function downloadTemplate()
    {
        $user = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('employee.salary.import')) {
            abort(403, 'Unauthorized');
        }

        $headers = [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_employee_salary.xlsx"',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Header row — 9 kolom
        $sheet->fromArray([[
            'employee_pengenal',
            'basic_salary',
            'position_allowance',
            'daily_rate',
            'meal_allowance',
            'house_allowance',
            'transport_allowance',
            'bpjs_kesehatan',
            'bpjs_ketenagakerjaan',
        ]], null, 'A1');

        // Contoh data PKWT
        $sheet->fromArray([[
            '2025',
            4200000,
            1800000,
            0,
            500000,
            300000,
            200000,
            0,
            0,
            0,
        ]], null, 'A2');

        // Contoh data DW
        $sheet->fromArray([[
            'EMP002',
            0,
            0,
            50000,
            0,
            0,
            0,
            0,
            0,
        ]], null, 'A3');

        // Style header — fix range jadi A1:J1 (10 kolom)
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1e293b'],
            ],
        ]);

        // Auto width — fix range jadi A sampai J (10 kolom)
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = tempnam(sys_get_temp_dir(), 'salary_template');
        $writer->save($filename);

        return response()->download($filename, 'template_employee_salary.xlsx', $headers)
            ->deleteFileAfterSend(true);
    }
}
