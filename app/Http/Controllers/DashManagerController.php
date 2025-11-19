<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Structuresnew;
use App\Models\Position;
use App\Models\Company;
use App\Models\Announcment;
use App\Models\Employee;
use App\Models\Departments;
use App\Models\Stores;
use Illuminate\Support\Facades\DB;

use App\Models\Banks;
use App\Models\Grading;
use Yajra\DataTables\DataTables;

class DashManagerController extends Controller
{
    public function indexteam()
    {
    $announcements = Announcment::orderBy('created_at', 'desc')->get();

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
    public function getTeams(Request $request, DataTables $dataTables)
    {
        $loggedEmployee = auth()->user()->Employee;

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
            ->whereHas('Employee', function ($q) use ($loggedEmployee) {
                $q->where('company_id', $loggedEmployee->company_id)
                    ->where('department_id', $loggedEmployee->department_id);
            })
            ->select(['id', 'employee_id'])
            ->get()
            ->map(function ($employee) {
                $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
                $employeeName = optional($employee->Employee)->employee_name;
                $employee->action = '
                <a href="' . route('Team.show', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="Show Employee: ' . e($employeeName) . '">
                    <i class="fas fa-eye text-secondary"></i>
                </a>';
                return $employee;
            });
        return DataTables::of($employees)
            ->addColumn('name_company', fn($e) => optional(optional($e->Employee)->company)->name ?? 'Empty')
            ->addColumn('grading_name', fn($e) => optional(optional($e->Employee)->grading)->grading_name ?? 'Empty')
            ->addColumn('name', fn($e) => optional(optional($e->Employee)->store)->name ?? 'Empty')
            ->addColumn('oldposition_name', fn($e) => optional(optional($e->Employee)->position)->name ?? 'Empty')
            ->addColumn('position_name', fn($e) => optional(optional($e->Employee->structuresnew)->position)->name ?? 'Empty')
            ->addColumn('department_name', fn($e) => optional(optional($e->Employee)->department)->department_name ?? 'Empty')
            ->addColumn('status_employee', fn($e) => optional($e->Employee)->status_employee ?? 'Empty')
            ->addColumn('employee_name', fn($e) => optional($e->Employee)->employee_name ?? 'Empty')
            ->addColumn('employee_pengenal', fn($e) => optional($e->Employee)->employee_pengenal ?? 'Empty')
            ->addColumn('created_at', fn($e) => optional($e->Employee)->created_at ?? 'Empty')
            ->addColumn('length_of_service', fn($e) => optional($e->Employee)->length_of_service ?? 'Empty')
            ->addColumn('status', fn($e) => optional($e->Employee)->status ?? 'Empty')
            ->rawColumns(['employee_pengenal','position_name', 'oldposition_name', 'status', 'department_name', 'company_name', 'created_at', 'employee_name', 'name', 'status_employee', 'grading_name', 'action'])
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
