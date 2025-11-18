<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Structuresnew;
use App\Models\Position;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Departments;
use App\Models\Stores;
use App\Models\Banks;
use App\Models\Grading;
use Yajra\DataTables\DataTables;

class DashManagerController extends Controller
{
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
            ->addColumn('created_at', fn($e) => optional($e->Employee)->created_at ?? 'Empty')
            ->addColumn('length_of_service', fn($e) => optional($e->Employee)->length_of_service ?? 'Empty')
            ->addColumn('status', fn($e) => optional($e->Employee)->status ?? 'Empty')
            ->rawColumns(['position_name', 'oldposition_name', 'status', 'department_name', 'company_name', 'created_at', 'employee_name', 'name', 'status_employee', 'grading_name', 'action'])
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
}
