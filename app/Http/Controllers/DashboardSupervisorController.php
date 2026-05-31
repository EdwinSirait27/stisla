<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Structuresnew;
use App\Models\Position;
use App\Models\Company;
use App\Models\Announcement;
use App\Models\Employee;
use App\Models\Departments;
use App\Models\Stores;
use App\Models\Leavebalance;
use App\Models\Fingerprints;
use App\Models\Overtimesubmissions;
use App\Models\Leaverequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Banks;
use App\Models\Grading;
class DashboardSupervisorController extends Controller
{
    private function getSubStructureIds($id)
    {
        $children = Structuresnew::where('parent_id', $id)->pluck('id')->toArray();
        $all = $children;

        foreach ($children as $childId) {
            $all = array_merge($all, $this->getSubStructureIds($childId));
        }

        return $all;
    }

   
     public function index()
    {
        $announcements = Announcement::with('user')
            ->orderBy('publish_date', 'desc')
            ->paginate(10);

        $employee = Auth::user()->employee;
        $companyId = $employee->company_id;
        $departmentId = $employee->department_id;

        // Ambil multi store user
        $multiStores = $employee->structureNew?->stores?->pluck('id')->toArray();
        $storeIds = !empty($multiStores) ? $multiStores : [$employee->store_id];

        // Hitung total karyawan
        $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])
            ->where('company_id', $companyId)
            ->where('department_id', $departmentId)
            ->whereIn('store_id', $storeIds)
            ->count();

        $totalEmployeespending = Employee::where('status', 'Pending')
            ->where('company_id', $companyId)
            ->where('department_id', $departmentId)
            ->whereIn('store_id', $storeIds)
            ->count();

        $employeeLogin = auth()->user()->employee;
        $pin = $employeeLogin->pin;
        $today = now()->format('Y-m-d');
        $lastWeek = now()->subDays(7)->format('Y-m-d');

        $presentCount = Fingerprints::whereBetween('scan_date', [$lastWeek, $today])
            ->where('inoutmode', 1)
            ->where('pin', $pin)
            ->distinct('scan_date')
            ->count();

        $employeelogin = $employee->id;
        $leavebalance = Leavebalance::with(['employees', 'leaves'])
            ->where('employee_id', $employeelogin)
            ->get();

        // Ambil pending leave dari bawahan berdasarkan tree struktur
        $myStructureId           = $employee->structure_id;
        $subordinateStructureIds = $myStructureId
            ? $this->getSubStructureIds($myStructureId)  // method ini sudah ada di controller
            : [];
 
        $subordinateEmployeeIds = Employee::whereIn('structure_id', $subordinateStructureIds)
            ->pluck('id')
            ->toArray();
 
        $subordinateBalanceIds = Leavebalance::whereIn('employee_id', $subordinateEmployeeIds)
            ->pluck('id')
            ->toArray();
 
        $pendingLeaves = Leaverequest::with([
                'leavebalance.employees',
                'leavebalance.leaves',
            ])
            ->whereIn('leave_balance_id', $subordinateBalanceIds)
            ->whereIn('status', ['Pending', 'Sent'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('pages.dashboardSupervisor.dashboardSupervisor', compact(
            'presentCount',
            'announcements',
            'totalEmployees',
            'leavebalance',
            'totalEmployeespending',
            'pendingLeaves'
        ));
    }
}
 //  public function index()
    // {
    //  $announcements = Announcement::with('user')
    //         ->orderBy('publish_date', 'desc')
    //         ->paginate(10);

    //     $employee = Auth::user()->employee;
    //     $companyId = $employee->company_id;
    //     $departmentId = $employee->department_id;

    //     // Ambil multi store user
    //     $multiStores = $employee->structureNew?->stores?->pluck('id')->toArray();
    //     $storeIds = !empty($multiStores) ? $multiStores : [$employee->store_id];

    //     // Hitung total karyawan
    //     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])
    //         ->where('company_id', $companyId)
    //         ->where('department_id', $departmentId)
    //         ->whereIn('store_id', $storeIds)
    //         ->count();

    //     $totalEmployeespending = Employee::where('status', 'Pending')
    //         ->where('company_id', $companyId)
    //         ->where('department_id', $departmentId)
    //         ->whereIn('store_id', $storeIds)
    //         ->count();

    //     $employeeLogin = auth()->user()->employee;
    //     $pin = $employeeLogin->pin;
    //     $today = now()->format('Y-m-d');
    //     $lastWeek = now()->subDays(7)->format('Y-m-d');

    //     $presentCount = Fingerprints::whereBetween('scan_date', [$lastWeek, $today])
    //         ->where('inoutmode', 1)
    //         ->where('pin', $pin)
    //         ->distinct('scan_date')
    //         ->count();

    //     $employeelogin = $employee->id;
    //     $leavebalance = Leavebalance::with(['employees', 'leaves'])
    //         ->where('employee_id', $employeelogin)
    //         ->get();

    //     // Ambil pending leave dari bawahan berdasarkan tree struktur
    //     $myStructureId           = $employee->structure_id;
    //     $subordinateStructureIds = $myStructureId
    //         ? $this->getSubStructureIds($myStructureId)  // method ini sudah ada di controller
    //         : [];
 
    //     $subordinateEmployeeIds = Employee::whereIn('structure_id', $subordinateStructureIds)
    //         ->pluck('id')
    //         ->toArray();
 
    //     $subordinateBalanceIds = Leavebalance::whereIn('employee_id', $subordinateEmployeeIds)
    //         ->pluck('id')
    //         ->toArray();
 
    //     $pendingLeaves = Leaverequest::with([
    //             'leavebalance.employees',
    //             'leavebalance.leaves',
    //         ])
    //         ->whereIn('leave_balance_id', $subordinateBalanceIds)
    //         ->whereIn('status', ['Pending', 'Sent'])
    //         ->orderBy('created_at', 'desc')
    //         ->take(5)
    //         ->get();

    //     return view('pages.dashboardSupervisor.dashboardSupervisor', compact(
    //         'presentCount',
    //         'announcements',
    //         'totalEmployees',
    //         'leavebalance',
    //         'totalEmployeespending',
    //         'pendingLeaves'
    //     ));

    // }