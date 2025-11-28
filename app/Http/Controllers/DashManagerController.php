<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Structuresnew;
use App\Models\Position;
use App\Models\Company;
use App\Models\Announcement;
use App\Models\Employee;
use App\Models\Departments;
use App\Models\Stores;
use App\Models\Fingerprints;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\Banks;
use App\Models\Grading;
use Yajra\DataTables\DataTables;

class DashManagerController extends Controller
{
    public function indexteam()
    {
    // $announcements = Announcment::orderBy('created_at', 'desc')->get();
$announcements = Announcement::with('user')
            ->orderBy('publish_date', 'desc')
            ->paginate(10);
    $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();


        return view('pages.dashboardTeam.dashboardTeam',compact('announcements'));
    }
    public function index()
    {
        return view('pages.dashboardManager.dashboardManager');
    }
    public function team()
    {
        return view('pages.Team.Team');
    }
    // public function getTeams(Request $request, DataTables $dataTables)
    // {
    //     $loggedEmployee = auth()->user()->Employee;

    //     $employees = User::with([
    //         'Employee.company',
    //         'Employee.store',
    //         'Employee.position',
    //         'Employee.structuresnew.position',
    //         'Employee.department',
    //         'Employee.grading',
    //         'Employee.employees',
    //         'Employee.structuresnew.company',
    //         'Employee.structuresnew'
    //     ])
    //         ->whereHas('Employee', function ($q) use ($loggedEmployee) {
    //             $q->where('company_id', $loggedEmployee->company_id)
    //                 ->where('department_id', $loggedEmployee->department_id);
    //         })
    //         ->select(['id', 'employee_id'])
    //         ->get()
    //         ->map(function ($employee) {
    //             $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
    //             $employeeName = optional($employee->Employee)->employee_name;
    //             $employee->action = '
    //             <a href="' . route('Team.show', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="Show Employee: ' . e($employeeName) . '">
    //                 <i class="fas fa-eye text-secondary"></i>
    //             </a>';
    //             return $employee;
    //         });
    //     return DataTables::of($employees)
    //         ->addColumn('name_company', fn($e) => optional(optional($e->Employee)->company)->name ?? 'Empty')
    //         ->addColumn('grading_name', fn($e) => optional(optional($e->Employee)->grading)->grading_name ?? 'Empty')
    //         ->addColumn('name', fn($e) => optional(optional($e->Employee)->store)->name ?? 'Empty')
    //         ->addColumn('oldposition_name', fn($e) => optional(optional($e->Employee)->position)->name ?? 'Empty')
    //         ->addColumn('position_name', fn($e) => optional(optional($e->Employee->structuresnew)->position)->name ?? 'Empty')
    //         ->addColumn('department_name', fn($e) => optional(optional($e->Employee)->department)->department_name ?? 'Empty')
    //         ->addColumn('status_employee', fn($e) => optional($e->Employee)->status_employee ?? 'Empty')
    //         ->addColumn('employee_name', fn($e) => optional($e->Employee)->employee_name ?? 'Empty')
    //         ->addColumn('employee_pengenal', fn($e) => optional($e->Employee)->employee_pengenal ?? 'Empty')
    //         ->addColumn('created_at', fn($e) => optional($e->Employee)->created_at ?? 'Empty')
    //         ->addColumn('length_of_service', fn($e) => optional($e->Employee)->length_of_service ?? 'Empty')
    //         ->addColumn('status', fn($e) => optional($e->Employee)->status ?? 'Empty')
    //         ->rawColumns(['employee_pengenal','position_name', 'oldposition_name', 'status', 'department_name', 'company_name', 'created_at', 'employee_name', 'name', 'status_employee', 'grading_name', 'action'])
    //         ->make(true);
    // }
    public function getTeams(Request $request, DataTables $dataTables)
{
    $loggedEmployee = auth()->user()->Employee;

    // ambil multi-store
    $submission = $loggedEmployee->structuresnew->submissionposition ?? null;
    $multiStores = $submission?->stores->pluck('id') ?? collect();

    $employees = User::with([
        'Employee.company',
        'Employee.store',
        'Employee.position',
        'Employee.structuresnew.position',
        'Employee.department',
        'Employee.grading',
        'Employee.employees',
        'Employee.structuresnew.company',
        'Employee.structuresnew'
    ])
    ->whereHas('Employee', function ($q) use ($loggedEmployee, $multiStores) {
$q->whereIn('status', ['Active', 'Pending']);
        // FILTER COMPANY (boleh tetap)
        $q->where('company_id', $loggedEmployee->company_id);

        // ================ FIX PALING PENTING ================
        if ($multiStores->isNotEmpty()) {
            // Manager Multi-Store → JANGAN filter department
            $q->whereIn('store_id', $multiStores);

        } else {
            // Manager Single Store → department masih relevan
            $q->where('department_id', $loggedEmployee->department_id)
              ->where('store_id', $loggedEmployee->store_id);
        }
    })
    ->select(['id', 'employee_id'])
    ->get()
    ->map(function ($employee) {

        $employee->id_hashed = substr(
            hash('sha256', $employee->id . env('APP_KEY')), 0, 8
        );

        $employeeName = optional($employee->Employee)->employee_name;

        $employee->action = '
            <a href="' . route('Team.show', $employee->id_hashed) . '" class="mx-3" 
               data-bs-toggle="tooltip" title="Show Employee: ' . e($employeeName) . '">
               <i class="fas fa-eye text-secondary"></i>
            </a>';

        return $employee;
    });

    return DataTables::of($employees)
        ->addColumn('name_company', fn($e) => $e->Employee->company->name ?? 'Empty')
        ->addColumn('grading_name', fn($e) => $e->Employee->grading->grading_name ?? 'Empty')
        ->addColumn('name', fn($e) => $e->Employee->store->name ?? 'Empty')
        ->addColumn('oldposition_name', fn($e) => $e->Employee->position->name ?? 'Empty')
        ->addColumn('position_name', fn($e) => $e->Employee->structuresnew->position->name ?? 'Empty')
        ->addColumn('department_name', fn($e) => $e->Employee->department->department_name ?? 'Empty')
        ->addColumn('status_employee', fn($e) => $e->Employee->status_employee ?? 'Empty')
        ->addColumn('employee_name', fn($e) => $e->Employee->employee_name ?? 'Empty')
        ->addColumn('employee_pengenal', fn($e) => $e->Employee->employee_pengenal ?? 'Empty')
        ->addColumn('created_at', fn($e) => $e->Employee->created_at ?? 'Empty')
        ->addColumn('length_of_service', fn($e) => $e->Employee->length_of_service ?? 'Empty')
        ->addColumn('status', fn($e) => $e->Employee->status ?? 'Empty')
        ->rawColumns(['action'])
        ->make(true);
}


    public function show($hashedId)
    {
        $employee = User::with(
            'Employee',
            'Employee.store',
            'Employee.grading',
            'Employee.department',
            'Employee.position',
            'Employee.bank',
            'Employee.employees',
            'Employee.structuresnew',
            'Employee.structuresnew.submissionposition'
        )->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$employee) {
            abort(404, 'Employee not found.');
        }
        $isManager = optional(optional($employee->Employee)->structuresnew)->is_manager;

        $structures = Structuresnew::with('company', 'department', 'store', 'position', 'submissionposition')
            ->where('id', optional($employee->Employee)->structure_id)
            ->get();

        $positions = Position::get();
        $companys = Company::get();
        $employees = Employee::where('status', 'Active')->pluck('employee_name', 'id');
        $departments = Departments::with('user.Employee')->get();
        $stores = Stores::with('user.Employee')->get();
        $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
        $child = ['0', '1', '2', '3', '4', '5'];
        $marriage = ['Yes', 'No'];
        $gender = ['Male', 'Female', 'MD'];
        $status = ['Pending', 'Inactive', 'On Leave', 'Mutation', 'Active', 'Resign'];
        $banks = Banks::get();
        $gradings = Grading::get();
        $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
        $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];

        return view('pages.Team.show', compact(
            'employee',
            'employees',
            'status_employee',
            'child',
            'companys',
            'stores',
            'marriage',
            'gender',
            'gradings',
            'status',
            'banks',
            'religion',
            'structures',
            'last_education',
            'positions',
            'departments',
            'hashedId',
            'isManager'
        ));
    }
private function getSubStructureIds($id)
{
    $children = Structuresnew::where('parent_id', $id)->pluck('id')->toArray();
    $all = $children;

    foreach ($children as $childId) {
        $all = array_merge($all, $this->getSubStructureIds($childId));
    }

    return $all;
}


public function getOrgChartDataTeam()
{
    $user = auth()->user();

    // Ambil struktur root milik employee yang login
    $rootId = $user->employee->structure_id ?? null;

    if (!$rootId) {
        return response()->json([]);
    }

    // Ambil semua anak-anak structure (recursive)
    $childIds = $this->getSubStructureIds($rootId);

    // Gabungkan root + seluruh anak
    $allowedIds = array_merge([$rootId], $childIds);

    $gradingPriority = [
        'Director' => 1,
        'Head' => 2,
        'Senior Manager' => 3,
        'Manager' => 4,
        'Assistant Manager' => 5,
        'Supervisor' => 6,
        'Staff' => 7,
        'Daily Worker' => 8,
    ];

    // Ambil struktur yang diizinkan
    $structures = Structuresnew::with([
        'parent',
        'employee',
        'employee.store',
        'submissionposition',
        'employee.grading',
        'submissionposition.positionRelation',
        'submissionposition.store',
        'secondarySupervisors',
        'secondarySupervisors.employee',
        'secondarySupervisors.submissionposition.positionRelation',
        'secondarySupervisors.submissionposition.store',
    ])
    ->whereIn('id', $allowedIds)
    ->get();

    // 🔥 Kumpulkan parent_id dari structures
    $parentIds = $structures->pluck('parent_id')->filter()->unique()->toArray();
    
    // 🔥 Kumpulkan secondary supervisor IDs dari tabel pivot
    $secondarySupervisorIds = DB::table('structure_supervisors')
        ->whereIn('structure_id', $allowedIds)
        ->pluck('supervisor_id')
        ->unique()
        ->toArray();

    // 🔥 Gabungkan dan filter hanya yang belum ada
    $missingIds = array_merge($parentIds, $secondarySupervisorIds);
    $missingIds = array_diff($missingIds, $allowedIds);
    $missingIds = array_filter($missingIds); // Hapus null/empty

    // 🔥 Ambil struktur yang missing (parent & secondary supervisors)
    if (!empty($missingIds)) {
        $additionalStructures = Structuresnew::with([
            'parent',
            'employee',
            'employee.store',
            'submissionposition',
            'employee.grading',
            'submissionposition.positionRelation',
            'submissionposition.store',
            'secondarySupervisors',
            'secondarySupervisors.employee',
            'secondarySupervisors.submissionposition.positionRelation',
            'secondarySupervisors.submissionposition.store',
        ])
        ->whereIn('id', $missingIds)
        ->get();

        // Gabungkan dengan structures yang sudah ada
        $structures = $structures->merge($additionalStructures);
    }

    // Map data
    $data = $structures->map(function ($s) use ($gradingPriority) {

        $gradingName = collect($s->employee)->pluck('grading.grading_name')->first() ?? 'Empty';
        $level = $gradingPriority[$gradingName] ?? 999;

        $secondaryData = $s->secondarySupervisors->map(function($secondary) {
            return [
                'id' => $secondary->id,
                'employee_name' => collect($secondary->employee)->pluck('employee_name')->join(', ') ?: 'Empty',
                'position' => optional(optional($secondary->submissionposition)->positionRelation)->name ?? 'Unknown',
                'location' => optional(optional($secondary->submissionposition)->store)->name ?? 'Empty',
            ];
        })->toArray();

        return [
            'id'         => $s->id,
            'pid'        => $s->parent_id,
            'Position'   => optional(optional($s->submissionposition)->positionRelation)->name ?? 'Unknown',
            'Employee'   => collect($s->employee)->pluck('employee_name')->join(', ') ?: 'Empty',
            'Grading'    => $gradingName,
            'Location'   => optional(optional($s->submissionposition)->store)->name ?? 'Empty',
            'status'     => $s->status,
            'photo'      => collect($s->employee)->pluck('photo')->first() ?? '/default-avatar.png',
            'level'      => $level,
            'secondary'  => $secondaryData,
        ];
    });

    $sortedData = $data->sortBy('level')->values();

    return response()->json($sortedData);
}
public function indexteamfingerprint()
{
    $user = auth()->user();

    $submission = $user->employee->structuresnew->submissionposition ?? null;

    $multiStores = $submission?->stores ?? collect();

    if ($multiStores->isNotEmpty()) {
        $stores = $multiStores->pluck('name', 'id');
    } else {
        $stores = Stores::orderBy('name')->pluck('name', 'id');
    }
    return view('pages.Teamfingerprint.Teamfingerprint', compact('stores'));
}
// public function getTeamfingerprints(Request $request)
// {
//     ini_set('memory_limit', '1024M');

//     // 1️⃣ Ambil multi-store dari submissionposition
//     $submission = auth()->user()->employee->structuresnew->submissionposition ?? null;
//     $multiStores = $submission?->stores->pluck('id') ?? collect();

//     $storeName = $request->input('store_name');
//     $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
//     $endDate = Carbon::parse($request->input('end_date', now()))->endOfDay();

//     // 2️⃣ Query employee
//     $employeesQuery = Employee::with(['position:id,name', 'store:id,name'])
//         ->select('pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'status_employee');

//     // --- FILTER MULTI-STORE ---
//     if ($multiStores->isNotEmpty()) {
//         // Manager multi store
//         $employeesQuery->whereIn('store_id', $multiStores);
//     } else {
//         // Manager single store
//         $loggedStoreId = auth()->user()->employee->store_id;
//         $employeesQuery->where('store_id', $loggedStoreId);
//     }

//     // 3️⃣ Jika user memilih store dari dropdown
//     if ($storeName) {
//         $employeesQuery->whereHas('store', function($q) use ($storeName, $multiStores) {
//             if ($multiStores->isNotEmpty()) {
//                 $q->where('name', $storeName)
//                   ->whereIn('id', $multiStores);
//             } else {
//                 $q->where('name', $storeName);
//             }
//         });
//     }

//     // 4️⃣ Ambil employee yang lolos filter
//     $employees = $employeesQuery->get()->keyBy('pin');
//     $pinList = $employees->keys();

//     // 5️⃣ Ambil fingerprint
//     $fingerprints = Fingerprints::with('devicefingerprints:device_name,sn')
//         ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
//         ->whereIn('pin', $pinList)
//         ->whereBetween('scan_date', [$startDate, $endDate])
//         ->orderBy('scan_date')
//         ->get();

//     // 6️⃣ Hitung total hari unik
//     $totalHariPerPin = $fingerprints->groupBy(fn($f) => $f->pin)
//         ->map(fn($items) =>
//             $items->pluck('scan_date')->map(fn($d) => Carbon::parse($d)->toDateString())->unique()->count()
//         );

//     // 7️⃣ Group berdasarkan PIN + Tanggal
//     $grouped = $fingerprints->groupBy(fn($f) =>
//         $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString()
//     );

//     // 8️⃣ Bentuk final data
//     $result = $grouped->map(function ($group) use ($employees, $totalHariPerPin) {

//         $first = $group->first();
//         $pin = $first->pin;
//         $scanDate = Carbon::parse($first->scan_date)->toDateString();

//         $employee = $employees->get($pin);
//         if (!$employee) return null;

//         $row = [
//             'pin' => $pin,
//             'employee_name' => $employee->employee_name ?? '-',
//             'employee_pengenal' => $employee->employee_pengenal ?? '-',
//             'status_employee' => $employee->status_employee ?? '-',
//             'name' => $employee->store->name ?? '-',
//             'position_name' => optional($employee->position)->name ?? '-',
//             'device_name' => optional($first->devicefingerprints)->device_name ?? '-',
//             'scan_date' => $scanDate,
//             'total_hari' => $totalHariPerPin[$pin] ?? 0,
//         ];

//         // Init kolom in_x dan combine_x
//         for ($i = 1; $i <= 10; $i++) {
//             $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
//         }

//         // isi berdasarkan inoutmode
//         $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
//             if ($mode >= 1 && $mode <= 10) {
//                 $firstItem = $items->sortBy('scan_date')->first();
//                 $row["in_$mode"] = Carbon::parse($firstItem->scan_date)->format('H:i:s');
//                 $row["device_$mode"] = optional($firstItem->devicefingerprints)->device_name ?? '';
//                 $row["combine_$mode"] = "{$row["in_$mode"]} {$row["device_$mode"]}";
//             }
//         });

//         // Durasi
//         $times = collect(range(1, 10))
//             ->map(fn($i) => $row["in_$i"])
//             ->filter()
//             ->sort()
//             ->values();

//         if ($times->count() >= 2) {
//             $start = Carbon::parse($times->first());
//             $end = Carbon::parse($times->last());
//             $minutes = $start->diffInMinutes($end);
//             $row['duration'] = sprintf(
//                 '%d hour%s %d minute%s',
//                 floor($minutes / 60), floor($minutes / 60) !== 1 ? 's' : '',
//                 $minutes % 60, $minutes % 60 !== 1 ? 's' : ''
//             );
//         } else {
//             $row['duration'] = 'invalid';
//         }

//         return $row;
//     })->filter()->values();

//     // 9️⃣ Return datatable
//     return DataTables::of($result)->make(true);
// }
public function getTeamfingerprints(Request $request)
{
    ini_set('memory_limit', '1024M');

    // Ambil employee login
    $employeeLogin = auth()->user()->employee;

    // Ambil multi-store (jika ada)
    $submission = $employeeLogin->structuresnew->submissionposition ?? null;
    $multiStores = $submission?->stores?->pluck('id') ?? collect();

    // Filter tanggal
    $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
    $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

    // Query employee dasar
    $employeesQuery = Employee::with([
            'position:id,name',
            'store:id,name',
            'department:id,department_name'
        ])
        ->select('pin', 'employee_name', 'employee_pengenal', 'position_id', 'department_id','store_id', 'status_employee')
        ->whereNotNull('pin'); // penting!

    // Filter: manager multi store
    if ($multiStores->isNotEmpty()) {
        $employeesQuery->whereIn('store_id', $multiStores);
    } else {
        $employeesQuery->where('store_id', $employeeLogin->store_id);
    }
$employeesQuery->where('department_id', $employeeLogin->department_id);

    // Filter store dari dropdown
    if ($storeName = $request->input('store_name')) {
        $storeIds = Stores::where('name', $storeName)->pluck('id');

        if ($multiStores->isNotEmpty()) {
            $storeIds = $storeIds->intersect($multiStores);
        }

        $employeesQuery->whereIn('store_id', $storeIds);
    }

    // Ambil employee
    $employees = $employeesQuery->get()->keyBy('pin');
    $pinList = $employees->keys();

    // Jika tidak ada PIN, langsung return kosong
    if ($pinList->isEmpty()) {
        return DataTables::of(collect())->make(true);
    }

    // Ambil fingerprint
    $fingerprints = Fingerprints::with('devicefingerprints:device_name,sn')
        ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
        ->whereIn('pin', $pinList)
        ->whereBetween('scan_date', [$startDate, $endDate])
        ->orderBy('scan_date')
        ->get();

    // Hitung total hari unik
    $totalHariPerPin = $fingerprints->groupBy('pin')
        ->map(fn($items) =>
            $items->pluck('scan_date')
                  ->map(fn($d) => Carbon::parse($d)->toDateString())
                  ->unique()
                  ->count()
        );

    // Group berdasarkan PIN + tanggal
    $grouped = $fingerprints->groupBy(fn($f) =>
        $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString()
    );

    // Bentuk hasil akhir
    $result = $grouped->map(function ($group) use ($employees, $totalHariPerPin) {

        $first = $group->first();
        $pin = $first->pin;
        $scanDate = Carbon::parse($first->scan_date)->toDateString();

        $employee = $employees[$pin] ?? null;
        if (!$employee) return null;

        // Row dasar
        $row = [
            'pin' => $pin,
            'employee_name' => $employee->employee_name ?? '-',
            'employee_pengenal' => $employee->employee_pengenal ?? '-',
            'status_employee' => $employee->status_employee ?? '-',
            'name' => $employee->store->name ?? '-',
            'position_name' => $employee->position->name ?? '-',
            'department_name' => $employee->department->department_name ?? '-',
            'device_name' => $first->devicefingerprints->device_name ?? '-',
            'scan_date' => $scanDate,
            'total_hari' => $totalHariPerPin[$pin] ?? 0,
        ];

        // Init in_x, device_x, combine_x
        for ($i = 1; $i <= 10; $i++) {
            $row["in_$i"] = null;
            $row["device_$i"] = null;
            $row["combine_$i"] = null;
        }

        // Isi fingerprint per mode
        $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row, $scanDate) {
            if ($mode >= 1 && $mode <= 10) {
                $firstItem = $items->sortBy('scan_date')->first();
                $time = Carbon::parse($firstItem->scan_date)->format('H:i:s');

                $row["in_$mode"] = $time;
                $row["device_$mode"] = $firstItem->devicefingerprints->device_name ?? '';
                $row["combine_$mode"] = trim("$time {$row["device_$mode"]}");
            }
        });

        // Hitung durasi
        $times = collect(range(1, 10))
            ->map(fn($i) => $row["in_$i"] ? $scanDate . ' ' . $row["in_$i"] : null)
            ->filter()
            ->sort()
            ->values();

        if ($times->count() >= 2) {
            $start = Carbon::parse($times->first());
            $end   = Carbon::parse($times->last());
            $minutes = $start->diffInMinutes($end);

            $row['duration'] = sprintf(
                '%d hour%s %d minute%s',
                floor($minutes / 60), floor($minutes / 60) !== 1 ? 's' : '',
                $minutes % 60, ($minutes % 60) !== 1 ? 's' : ''
            );
        } else {
            $row['duration'] = 'invalid';
        }

        return $row;
    })->filter()->values();

    return DataTables::of($result)->make(true);
}
// public function getTeamfingerprints(Request $request)
// {
//     ini_set('memory_limit', '1024M');

//     // Ambil employee login
//     $employeeLogin = auth()->user()->employee;

//     // Ambil multi-store (jika ada)
//     $submission = $employeeLogin->structuresnew->submissionposition ?? null;
//     $multiStores = $submission?->stores?->pluck('id') ?? collect();

//     // Filter tanggal
//     $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
//     $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

//     // Query employee dasar
//     $employeesQuery = Employee::with([
//             'position:id,name',
//             'store:id,name'
//         ])
//         ->select('pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'department_id', 'status_employee')
//         ->whereNotNull('pin'); // penting!

//     // Filter multi store
//     if ($multiStores->isNotEmpty()) {
//         $employeesQuery->whereIn('store_id', $multiStores);
//     } else {
//         $employeesQuery->where('store_id', $employeeLogin->store_id);
//     }
// $employeesQuery->where('department_id', $employeeLogin->department_id);
//     // Filter store dari dropdown
//     if ($storeName = $request->input('store_name')) {
//         $storeIds = Stores::where('name', $storeName)->pluck('id');

//         if ($multiStores->isNotEmpty()) {
//             $storeIds = $storeIds->intersect($multiStores);
//         }

//         $employeesQuery->whereIn('store_id', $storeIds);
//     }

//     // 🔥 Tambahkan filter department_id di sini
//     if ($departmentId = $request->input('department_id')) {
//         $employeesQuery->where('department_id', $departmentId);
//     }

//     $employees = $employeesQuery->get()->keyBy('pin');
//     $pinList = $employees->keys();

//     if ($pinList->isEmpty()) {
//         return DataTables::of(collect())->make(true);
//     }
//     // Ambil fingerprint
//     $fingerprints = Fingerprints::with('devicefingerprints:device_name,sn')
//         ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
//         ->whereIn('pin', $pinList)
//         ->whereBetween('scan_date', [$startDate, $endDate])
//         ->orderBy('scan_date')
//         ->get();
//     // Hitung total hari unik
//     $totalHariPerPin = $fingerprints->groupBy('pin')
//         ->map(fn($items) =>
//             $items->pluck('scan_date')
//                   ->map(fn($d) => Carbon::parse($d)->toDateString())
//                   ->unique()
//                   ->count()
//         );

//     // Group berdasarkan PIN + tanggal
//     $grouped = $fingerprints->groupBy(fn($f) =>
//         $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString()
//     );

//     // Bentuk hasil akhir
//     $result = $grouped->map(function ($group) use ($employees, $totalHariPerPin) {

//         $first = $group->first();
//         $pin = $first->pin;
//         $scanDate = Carbon::parse($first->scan_date)->toDateString();

//         $employee = $employees[$pin] ?? null;
//         if (!$employee) return null;

//         // Row dasar
//         $row = [
//             'pin' => $pin,
//             'employee_name' => $employee->employee_name ?? '-',
//             'employee_pengenal' => $employee->employee_pengenal ?? '-',
//             'status_employee' => $employee->status_employee ?? '-',
//             'name' => $employee->store->name ?? '-',
//             'department_name' => $employee->department->department_name ?? '-',
//             'position_name' => $employee->position->name ?? '-',
//             'device_name' => $first->devicefingerprints->device_name ?? '-',
//             'scan_date' => $scanDate,
//             'total_hari' => $totalHariPerPin[$pin] ?? 0,
//         ];

//         // Init in_x, device_x, combine_x
//         for ($i = 1; $i <= 10; $i++) {
//             $row["in_$i"] = null;
//             $row["device_$i"] = null;
//             $row["combine_$i"] = null;
//         }

//         // Isi fingerprint per mode
//         $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row, $scanDate) {
//             if ($mode >= 1 && $mode <= 10) {
//                 $firstItem = $items->sortBy('scan_date')->first();
//                 $time = Carbon::parse($firstItem->scan_date)->format('H:i:s');

//                 $row["in_$mode"] = $time;
//                 $row["device_$mode"] = $firstItem->devicefingerprints->device_name ?? '';
//                 $row["combine_$mode"] = trim("$time {$row["device_$mode"]}");
//             }
//         });

//         // Hitung durasi
//         $times = collect(range(1, 10))
//             ->map(fn($i) => $row["in_$i"] ? $scanDate . ' ' . $row["in_$i"] : null)
//             ->filter()
//             ->sort()
//             ->values();

//         if ($times->count() >= 2) {
//             $start = Carbon::parse($times->first());
//             $end   = Carbon::parse($times->last());
//             $minutes = $start->diffInMinutes($end);

//             $row['duration'] = sprintf(
//                 '%d hour%s %d minute%s',
//                 floor($minutes / 60), floor($minutes / 60) !== 1 ? 's' : '',
//                 $minutes % 60, ($minutes % 60) !== 1 ? 's' : ''
//             );
//         } else {
//             $row['duration'] = 'invalid';
//         }

//         return $row;
//     })->filter()->values();

//     return DataTables::of($result)->make(true);
// }











//     public function getOrgChartDataTeam()
// {
//     $user = auth()->user();

//     // Ambil struktur root milik employee yang login
//     $rootId = $user->employee->structure_id ?? null;

//     if (!$rootId) {
//         return response()->json([]);
//     }

//     // Ambil semua anak-anak structure (recursive)
//     $childIds = $this->getSubStructureIds($rootId);

//     // Gabungkan root + seluruh anak
//     $allowedIds = array_merge([$rootId], $childIds);

//     $gradingPriority = [
//         'Director' => 1,
//         'Head' => 2,
//         'Senior Manager' => 3,
//         'Manager' => 4,
//         'Assistant Manager' => 5,
//         'Supervisor' => 6,
//         'Staff' => 7,
//         'Daily Worker' => 8,
//     ];

//     // FILTER disini → hanya structure subtree milik user
//     $data = Structuresnew::with([
//         'parent',
//         'employee',
//         'employee.store',
//         'submissionposition',
//         'employee.grading',
//         'submissionposition.positionRelation',
//         'submissionposition.store',
//         'secondarySupervisors',
//         'secondarySupervisors.employee',
//         'secondarySupervisors.submissionposition.positionRelation',
//     ])
//     ->whereIn('id', $allowedIds) // ⬅🔥 hanya struktur user + anak2nya
//     ->get()
//     ->map(function ($s) use ($gradingPriority) {

//         $gradingName = collect($s->employee)->pluck('grading.grading_name')->first() ?? 'Empty';
//         $level = $gradingPriority[$gradingName] ?? 999;

//         $secondaryData = $s->secondarySupervisors->map(function($secondary) {
//             return [
//                 'id' => $secondary->id,
//                 'employee_name' => collect($secondary->employee)->pluck('employee_name')->join(', ') ?: 'Empty',
//                 'position' => optional(optional($secondary->submissionposition)->positionRelation)->name ?? 'Unknown',
//             ];
//         })->toArray();

//         return [
//             'id'         => $s->id,
//             'pid'        => $s->parent_id,
//             'Position'   => optional(optional($s->submissionposition)->positionRelation)->name ?? 'Unknown',
//             'Employee'   => collect($s->employee)->pluck('employee_name')->join(', ') ?: 'Empty',
//             'Grading'    => $gradingName,
//             'Location'   => optional(optional($s->submissionposition)->store)->name ?? 'Empty',
//             'status'     => $s->status,
//             'photo'      => collect($s->employee)->pluck('photo')->first() ?? '/default-avatar.png',
//             'level'      => $level,
//             'secondary'  => $secondaryData,
//         ];
//     });

//     $sortedData = $data->sortBy('level')->values();

//     return response()->json($sortedData);
// }

}
