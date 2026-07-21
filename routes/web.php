<?php

use App\Http\Controllers\dashboardAdminController;
use App\Http\Controllers\DashboardManagerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardHRController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PayrollEmailController;
use App\Http\Controllers\EmployeeImportController;
use App\Http\Controllers\PayrollsController;
use App\Http\Controllers\DashboardSupervisorController;
use App\Http\Controllers\UserprofileController;
use App\Http\Controllers\DashManagerController;
use App\Http\Controllers\AttendanceMobileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\BanksController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\FingerspotController;
use App\Http\Controllers\AttendanceimportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FingerprintsController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\StructureSubmissionController;
use App\Http\Controllers\PHController;
use App\Http\Controllers\GradinglistController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\Editedfingerprints;
use App\Http\Controllers\LeaverequestController;
use App\Http\Controllers\DashboardHeadController;
use App\Http\Controllers\PositionreqController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SubmissionsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardHumanController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\LeavesController;
use App\Http\Controllers\LeavebalancesController;
use App\Http\Controllers\MasterSubmissionController;
use App\Http\Controllers\PositionapprovalController;
use App\Http\Controllers\SKController;
use App\Http\Controllers\SktemplateController;
use App\Http\Controllers\LeavetypesController;
use App\Http\Controllers\StructuresnewController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\ShiftsController;
use App\Http\Controllers\PayrollcomponentsController;
use App\Http\Controllers\FingerprintrecapController;
use App\Http\Controllers\ManualRecapController;
use App\Http\Controllers\AutoRosterController;
use App\Http\Controllers\AutoRosterOtherStoreController;
use App\Http\Controllers\ToilController;
use App\Http\Controllers\ToilLeaveRequestsController;
use App\Http\Controllers\AssetCategoriesController;
use App\Http\Controllers\OvertimesubmissionsController;
use App\Http\Controllers\SkLetterController;
use App\Http\Controllers\UserrnrController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\EmployeePositionandAtasanController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmployeeTrainingController;
use App\Http\Controllers\OvertimeRateController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\DB;

use App\Models\Contract;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/change-password', [UserprofileController::class, 'indexpassword'])
        ->name('pages.change-password');
    Route::put('/change-password/update', [UserprofileController::class, 'updatePassword'])
        ->name('change-password.update');
    // ── Logout ──
    Route::match(['GET', 'POST'], '/logout', [LoginController::class, 'destroy'])
        ->name('logout');
          Route::get('/two-factor/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('/two-factor/setup/confirm', [TwoFactorController::class, 'confirmSetup'])->name('2fa.setup.confirm');
    Route::get('/two-factor/recovery-codes', [TwoFactorController::class, 'showRecoveryCodes'])->name('2fa.recovery-codes');
});
Route::middleware(['auth', 'role:Admin'])->group(function () {
    
    Route::post('/users/{user}/2fa/disable', [TwoFactorController::class, 'adminDisable'])->name('admin.2fa.disable');
    Route::post('/users/{user}/2fa/toggle-required', [TwoFactorController::class, 'adminToggleRequired'])->name('admin.2fa.toggle-required');
    });

Route::middleware(['auth', 'force.password.change', 'role:Admin|HeadHR|HR|Human|Manager|Director|Supervisor|Training'])->group(function () {
    
    Route::put('/profile/switch-role', [UserprofileController::class, 'switchRole'])->name('profile.switchRole');
    Route::get('/feature-profile', [UserprofileController::class, 'index'])
        ->name('pages.feature-profile');
    Route::post('/savesign', [UserprofileController::class, 'save'])->name('save.signature');
    Route::get('/rnr', [UserrnrController::class, 'index'])
        ->name('pages.rnr');
    Route::get('/my-sk-letter/{id}/download', [UserprofileController::class, 'downloadSkLetter'])
        ->middleware('auth')
        ->name('my-sk-letter.download');

    // routes/web.php
    // Route::get('/employee-photos/{filename}', [UserprofileController::class, 'servePhoto'])
    //     ->name('useremployee.photo');
    // Route::get('/employee-signatures-photos/{filename}', [UserprofileController::class, 'serveSignature'])
    //     ->name('useremployeesignature.photo');
    // Route::get('/employee-kk-photos/{filename}', [UserprofileController::class, 'servePhotokk'])
    //     ->name('useremployeekk.photo');
    // Route::get('/employee-ktp-photos/{filename}', [UserprofileController::class, 'servePhotoktp'])
    //     ->name('useremployeektp.photo');
//     Route::get('/employee-photos/{filename}', [UserprofileController::class, 'servePhoto'])
//     ->name('useremployee.photo');
// Route::get('/employee-signatures-photos/{filename}', [UserprofileController::class, 'serveSignature'])
//     ->name('useremployeesignature.photo');
// Route::get('/employee-kk-photos/{filename}', [UserprofileController::class, 'servePhotokk'])
//     ->name('useremployeekk.photo');
// Route::get('/employee-ktp-photos/{filename}', [UserprofileController::class, 'servePhotoktp'])
//     ->name('useremployeektp.photo');
    Route::get('/employee-photos/{filename}', [UserprofileController::class, 'servePhoto'])
        ->name('useremployee.photo');
    Route::get('/employee-signatures-photos/{filename}', [UserprofileController::class, 'serveSignature'])
        ->name('useremployeesignature.photo');
    Route::get('/employee-kk-photos/{filename}', [UserprofileController::class, 'servePhotokk'])
        ->name('useremployeekk.photo');
    Route::get('/employee-ktp-photos/{filename}', [UserprofileController::class, 'servePhotoktp'])
        ->name('useremployeektp.photo');

    Route::put('/feature-profile/update', [UserprofileController::class, 'updateemailtelpphotos'])
        ->name('feature-profile.update');
    Route::get('/profile/documents/{id}/download', [UserprofileController::class, 'downloadDocument'])
        ->name('profile.documents.download');

    // ── Dashboard Admin & Users Management ──
    Route::group(['middleware' => ['permission:dashboardAdmin']], function () {
        Route::get('/dashboardAdmin', [DashboardAdminController::class, 'index'])
            ->name('pages.dashboardAdmin');
        Route::get('/dashboardAdmin/edit/{hashedId}', [dashboardAdminController::class, 'edit'])->name('dashboardAdmin.edit');
        Route::get('/dashboardAdmin/show/{hashedId}', [dashboardAdminController::class, 'show'])->name('dashboardAdmin.show');
        Route::put('/dashboardAdmin/{hashedId}', [dashboardAdminController::class, 'update'])->name('dashboardAdmin.update');
        Route::match(['GET', 'POST'], '/users/users', [dashboardAdminController::class, 'getUsers'])->name('users.users');
        Route::post('/users/bulk-update-role', [dashboardAdminController::class, 'bulkUpdateRole'])->name('users.bulkUpdateRole');
    });
    Route::group(['middleware' => ['permission:dashboardSupervisor']], function () {
        // Route::get('/dashboardSupervisor', dashboardSupervisorController::class, 'index'])->name('pages.dashboardSupervisor');
        Route::get('/dashboardSupervisor', [DashboardSupervisorController::class, 'index'])
            ->name('pages.dashboardSupervisor');
    });
    // ── Activity (catatan: tetap pakai permission:dashboardAdmin sesuai original) ──
    Route::group(['middleware' => ['permission:dashboardAdmin']], function () {
        // Route::get('/Activity', [ActivityController::class, 'index'])->name('pages.Activity');
        // Route::get('/Activity/show/{hashedId}', [ActivityController::class, 'show'])->name('Activity.show');
        // Route::get('/activity/activity', [ActivityController::class, 'getActivity'])->name('activity.activity');
        // Route::get('/activity1/activity1', [ActivityController::class, 'getActivity1'])->name('activity1.activity1');
    });
    // ── Submissions ──
    Route::middleware(['auth'])->group(function () {
        Route::post('/Submissions', [SubmissionsController::class, 'store'])->name('Submissions.store');
        Route::post('/Leaverequest', [LeaverequestController::class, 'store'])->name('Leaverequest.store');
    });
    // ── Roles & Permissions ──
    Route::group(['middleware' => ['permission:ManageRolesPermissions']], function () {
        Route::get('/roles', [RoleController::class, 'index'])
            ->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/edit/{hashedId}', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{hashedId}', [RoleController::class, 'update'])->name('roles.update');
        Route::get('/role/role', [RoleController::class, 'getRoles'])->name('role.role');
        Route::get('/permissions', [PermissionController::class, 'index'])
            ->name('permissions.index');
        Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('/permissions/edit/{hashedId}', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/permissions/{hashedId}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::get('/permissions/permissions', [PermissionController::class, 'getPermissions'])->name('permissions.permissions');
    });
    // ── Dashboard HR ──
    Route::group(['middleware' => ['permission:dashboardHR']], function () {
        Route::get('/dashboardHR', [DashboardHRController::class, 'index'])
            ->name('pages.dashboardHR');
        Route::get('/dashboardHR/data', [DashboardHRController::class, 'getMonthlyData'])->name('dashboardHR.data');
        Route::get('/announcements/announcements', [DashboardHRController::class, 'getAnnouncements'])->name('announcements.announcements');
        Route::post('/dashboardHR', [DashboardHRController::class, 'store'])->name('dashboardHR.store');
        Route::get('/dashboard/employee-by-department', [DashboardHRController::class, 'employeeByDepartment']);
        Route::get('/dashboard/employee-by-company', [DashboardHRController::class, 'employeeByCompany']);
        Route::get('/dashboard/employee-by-los', [DashboardHRController::class, 'employeeByLengthOfService']);
    });

    // ── Employee ──
    Route::group(['middleware' => ['permission:ManageEmployee|ManageEmployeeSPVManager|ViewEmployee']], function () {
        Route::get('/Employee', [EmployeeController::class, 'index'])
            ->name('pages.Employee');
        Route::get('/Employee/show/{hashedId}', [EmployeeController::class, 'show'])->name('Employee.show');
        Route::get('/employees/employees', [EmployeeController::class, 'getEmployees'])->name('employees.employees');
    });
    Route::group(['middleware' => ['permission:ManageEmployee']], function () {

        Route::get('Employee/create', [EmployeeController::class, 'create'])->name('Employee.create');
        Route::post('/Employee', [EmployeeController::class, 'store'])->name('Employee.store');
        Route::get('/data/data', [EmployeeController::class, 'getActivities'])->name('data.data');

        Route::get('bagan/data', [EmployeeController::class, 'getBagan'])->name('employee.bagan');
        Route::get('/Employee/edit/{hashedId}', [EmployeeController::class, 'edit'])->name('Employee.edit');
        Route::put('/Employee/{hashedId}', [EmployeeController::class, 'update'])->name('Employee.update');
        Route::get('/employees/export', [EmployeeController::class, 'exportEmployees'])->name('Employee.export');
        // Route::post('/employees/transfer-all-to-payroll', [EmployeeController::class, 'transferAllToPayroll'])->name('employees.transferAllToPayroll');

        Route::get('/Employeeall', [EmployeeController::class, 'indexall'])
            ->name('pages.Employeeall');
        Route::match(['GET', 'POST'], '/employeesall/employeesall', [EmployeeController::class, 'getEmployeesall'])->name('employeesall.employeesall');
        Route::get('/employeesall/exportall', [EmployeeController::class, 'exportEmployeesall'])->name('Employeeall.exportall');

        Route::get('/Import', [EmployeeImportController::class, 'index'])
            ->name('pages.Import');
        Route::post('/Import', [EmployeeImportController::class, 'import'])->name('Import.employee');
        Route::get('/Importuser', [EmployeeImportController::class, 'indexuser'])
            ->name('pages.Importuser');
        Route::post('/Importuser', [EmployeeImportController::class, 'importuser'])->name('Importuser.user');


        // Route::get('/employee-photos/{filename}',    [EmployeeController::class, 'servePhoto'])->name('employee.photo');
        // Route::get('/employee-signatures-photos/{filename}', [EmployeeController::class, 'serveSignature'])->name('employee.signature');
        // Route::get('/employee-kk-photos/{filename}',       [EmployeeController::class, 'serveKkPhoto'])->name('employee.kk');
        // Route::get('/employee-ktp-photos/{filename}',      [EmployeeController::class, 'serveKtpPhoto'])->name('employee.ktp');
        // Route::get('employee/photo/{filename}', [EmployeeController::class, 'servePhoto'])
        //     ->name('employee.serve.photo');
        Route::get('Employee/{hashedId}/document/{documentId}/download', [EmployeeController::class, 'downloadDocument'])
            ->name('Employee.document.download');
    });



    // ── Employee training──

    Route::group(['middleware' => ['permission:ManageEmployeeTraining']], function () {
        Route::get('/employee-training', [EmployeeTrainingController::class, 'index'])
            ->name('pages.employee-training');
        Route::get('/employeestraining/employeestraining', [EmployeeTrainingController::class, 'getEmployeeTrainings'])->name('employeestraining.employeestraining');
        Route::get('/employeestraining/export', [EmployeeTrainingController::class, 'exportTraingingEmployees'])->name('Employeetraining.export');
    });

    Route::prefix('employees')->name('Employee.')->group(function () {

        Route::get('/bulk', [EmployeePositionandAtasanController::class, 'bulkIndex'])
            ->name('bulk');

        Route::get('/position-atasans', [EmployeePositionandAtasanController::class, 'getEmployeePositionandAtasans'])
            ->name('positionAtasans');

        Route::post('/bulk-assign-position', [EmployeePositionandAtasanController::class, 'bulkAssignPosition'])
            ->name('bulkAssignPosition');
        Route::post('/bulk-delete-position', [EmployeePositionandAtasanController::class, 'bulkDeletePosition'])
            ->name('bulkDeletePosition');

        Route::post('/bulk-assign-atasan', [EmployeePositionandAtasanController::class, 'bulkAssignAtasan'])
            ->name('bulkAssignAtasan');
        Route::post('/bulk-delete-atasan', [EmployeePositionandAtasanController::class, 'bulkDeleteAtasan'])
            ->name('bulkDeleteAtasan');
    });



    // Route::group(['middleware' => ['permission:ManageEmployee']], function () {
    //     Route::get('/data/data', [EmployeeController::class, 'getActivities'])->name('data.data');
    //     Route::get('/Employee', [EmployeeController::class, 'index'])
    //         ->name('pages.Employee');
    //     Route::get('bagan/data', [EmployeeController::class, 'getBagan'])->name('employee.bagan');

    //     Route::get('Employee/create', [EmployeeController::class, 'create'])->name('Employee.create');
    //     Route::post('/Employee', [EmployeeController::class, 'store'])->name('Employee.store');
    //     Route::get('/Employee/edit/{hashedId}', [EmployeeController::class, 'edit'])->name('Employee.edit');
    //     Route::get('/Employee/show/{hashedId}', [EmployeeController::class, 'show'])->name('Employee.show');
    //     Route::put('/Employee/{hashedId}', [EmployeeController::class, 'update'])->name('Employee.update');
    //     Route::get('/employees/employees', [EmployeeController::class, 'getEmployees'])->name('employees.employees');
    //     Route::get('/employees/export', [EmployeeController::class, 'exportEmployees'])->name('Employee.export');
    //     // Route::post('/employees/transfer-all-to-payroll', [EmployeeController::class, 'transferAllToPayroll'])->name('employees.transferAllToPayroll');

    //     Route::get('/Employeeall', [EmployeeController::class, 'indexall'])
    //         ->name('pages.Employeeall');
    //     Route::match(['GET', 'POST'], '/employeesall/employeesall', [EmployeeController::class, 'getEmployeesall'])->name('employeesall.employeesall');
    //     Route::get('/employeesall/exportall', [EmployeeController::class, 'exportEmployeesall'])->name('Employeeall.exportall');

    //     Route::get('/Import', [EmployeeImportController::class, 'index'])
    //         ->name('pages.Import');
    //     Route::post('/Import', [EmployeeImportController::class, 'import'])->name('Import.employee');
    //     Route::get('/Importuser', [EmployeeImportController::class, 'indexuser'])
    //         ->name('pages.Importuser');
    //     Route::post('/Importuser', [EmployeeImportController::class, 'importuser'])->name('Importuser.user');


    //     Route::get('/employee-photo/{filename}',    [EmployeeController::class, 'servePhoto'])->name('employee.photo');
    //     Route::get('/employee-ktp/{filename}',      [EmployeeController::class, 'serveKtpPhoto'])->name('employee.ktp');
    //     Route::get('/employee-kk/{filename}',       [EmployeeController::class, 'serveKkPhoto'])->name('employee.kk');
    //     Route::get('/employee-signature/{filename}', [EmployeeController::class, 'serveSignature'])->name('employee.signature');
    //     Route::get('employee/photo/{filename}', [EmployeeController::class, 'servePhoto'])
    //         ->name('employee.serve.photo')
    //         ->middleware('auth');
    // });



    Route::group(['middleware' => ['permission:ManageContracts|VEUSomeContracts']], function () {
        Route::get('/datacontracts/datacontracts', [ContractController::class, 'getActivities'])->name('datacontracts.datacontracts');

        Route::get('/contract', [ContractController::class, 'index'])
            ->name('contract');
        Route::get('contract/create', [ContractController::class, 'create'])->name('createcontract');
        Route::post('/contract', [ContractController::class, 'store'])->name('storecontract');
        Route::get('/contract/edit/{id}', [ContractController::class, 'edit'])->name('editcontract');
        Route::get('/contract/show/{id}', [ContractController::class, 'show'])->name('showcontract');
        Route::put('/contract/{id}', [ContractController::class, 'update'])->name('updatecontract');
        Route::get('/contracts/contracts', [ContractController::class, 'getContracts'])->name('contracts.contracts');
        Route::post('/contract/check-password', [ContractController::class, 'checkPassword'])
            ->name('contract.password.ajax');
    });
    Route::group(['middleware' => ['permission:ManageFingerspotSPVManager|ManageFingerspot']], function () {
        Route::get('/fingerprints/export', [FingerprintsController::class, 'exportfingerprints'])
            ->name('fingerprints.exportfingerprints');
            Route::post('fingerprints/bulk-status', [FingerprintsController::class, 'bulkStatus'])
    ->name('fingerprints.bulkStatus');
    });
    Route::group(['middleware' => ['permission:ManageFingerspotSPVManager|ManageFingerspot|ViewFingerspot']], function () {
        Route::get('/Fingerprints', [FingerprintsController::class, 'index'])
            ->name('pages.Fingerprints');
        Route::match(['GET', 'POST'], '/fingerprints/fingerprints', [FingerprintsController::class, 'getFingerprints'])->name('fingerprints.fingerprints');
        Route::post('fingerprints/status/{id}', [FingerprintsController::class, 'updateStatus'])
    ->name('fingerprints.updateStatus');
      Route::get('/Fingerprints/edit/{pin}', [FingerprintsController::class, 'editFingerprint'])->name('pages.Fingerprints.edit');
     Route::get('fingerprints/show/{pin}', [FingerprintsController::class, 'showFingerprint'])
    ->name('pages.Fingerprints.show');

        Route::put('/fingerprints/{pin}/{scan_date}', [FingerprintsController::class, 'updateFingerprint'])->name('Fingerprints.update');
        Route::delete('fingerprints/attachment', [FingerprintsController::class, 'deleteAttachment'])
    ->name('fingerprints.deleteAttachment');
    });
   

    Route::group(['middleware' => ['permission:ManageFingerspot']], function () {
        Route::get('fingerprints/log', [FingerprintsController::class, 'getLog'])->name('fingerprints.log');

        Route::get('/Fingerspot', [FingerspotController::class, 'index'])
            ->name('pages.Fingerspot');
        Route::get('/fingerspot/fingerspot', [FingerspotController::class, 'getFingerspot'])->name('fingerspot.fingerspot');
        Route::get('/Importfingerspot', [FingerspotController::class, 'indexfingerspot'])
            ->name('pages.Importfingerspot');
        Route::post('/Importfingerspot', [FingerspotController::class, 'sinkronkanPIN'])->name('Importfingerspot.fingerspot');
        Route::get('/fingerspot/fingerspot', [FingerspotController::class, 'getPins'])->name('fingerspot.fingerspot');
        Route::get('/Fingerspot/downloadfingerspot/{filename}', [FingerspotController::class, 'downloadfingerspot'])->name('Fingerspot.downloadfingerspot');

        Route::get('/Importattendance', [AttendanceimportController::class, 'indexattendances'])
            ->name('pages.Importattendance');
        Route::post('/Importattendance', [AttendanceimportController::class, 'importattendance'])->name('Importattendance.attendance');
        Route::get('/Importattendance/downloadattendance/{filename}', [AttendanceimportController::class, 'downloadattendance'])->name('Importattendance.downloadattendance');

        Route::get('/attendance/attendance', [AttendanceController::class, 'getAttendances'])->name('attendance.attendance');
        Route::get('/Attendance', [AttendanceController::class, 'index'])
            ->name('pages.Attendance');
        Route::post('/attendance/summary', [AttendanceController::class, 'storeAttendanceSummary'])->name('attendance.summary');

        Route::get('/Attendanceall', [AttendanceController::class, 'indexattendance'])
            ->name('pages.Attendanceall');
        Route::match(['GET', 'POST'], '/attendanceall/attendanceall', [AttendanceController::class, 'getAttendancealls'])->name('attendanceall.attendanceall');

       Route::get('/Fingerprints/total-hari', [FingerprintsController::class, 'getTotalHariBekerja'])->name('Fingerprints.totalHari');
        Route::post('/fingerprints/recap', [FingerprintsController::class, 'recap'])->name('fingerprints.recap');

        Route::match(['GET', 'POST'], '/fingerprints/manual-added', [FingerprintsController::class, 'getManualAdded'])
            ->name('fingerprints.manual-added');

        // ── Endpoint list employees untuk dropdown Add Recap ──
        // Route::get('/fingerprints/employee-list', function (\Illuminate\Http\Request $request) {
        //     $query = \App\Models\Employee::with('store:id,name')
        //         ->select('id', 'employee_name', 'pin', 'store_id')
        //         ->whereNotNull('pin')
        //         ->whereNull('deleted_at');

        //     if ($request->store_name) {
        //         $query->whereHas('store', fn($q) => $q->where('name', $request->store_name));
        //     }

        //     return response()->json([
        //         'data' => $query->orderBy('employee_name')->get()->map(fn($e) => [
        //             'id'    => $e->id,
        //             'name'  => $e->employee_name,
        //             'pin'   => $e->pin,
        //             'store' => $e->store->name ?? '-',
        //         ])
        //     ]);
        // })->name('fingerprints.employee-list');
        Route::get('/fingerprints/employee-list', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\Employee::with([
                'store' => fn($q) => $q->wherePivot('is_primary', true)->select('stores_tables.id', 'stores_tables.name'),
            ])
                ->select('id', 'employee_name', 'pin','status')
                ->whereNotNull('pin')
                ->whereNull('deleted_at');

            if ($request->store_name) {
                $query->whereExists(function ($q) use ($request) {
                    $q->select(DB::raw(1))
                        ->from('employee_stores')
                        ->join('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')
                        ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                        ->where('stores_tables.name', $request->store_name);
                });
            }

            return response()->json([
                'data' => $query->orderBy('employee_name')->get()->map(fn($e) => [
                    'id'    => $e->id,
                    'name'  => $e->employee_name,
                    'status'  => $e->status,
                    'pin'   => $e->pin,
                    'store' => $e->store->first()?->name ?? 'empty', // ← pivot returns collection
                ])
            ]);
        })->name('fingerprints.employee-list');

        // ── Edited Fingerprints ──
        Route::get('/Editedfinger', [Editedfingerprints::class, 'index'])
            ->name('pages.Editedfinger');
        Route::get('/fingerprints/attachment/{id}', [Editedfingerprints::class, 'showAttachment'])
            ->name('fingerprints.attachment');
        Route::match(['GET', 'POST'], '/editedfinger/editedfinger', [Editedfingerprints::class, 'getEditedfingerprints'])->name('editedfinger.editedfinger');
        Route::prefix('manual-recap')->name('manual-recap.')->middleware(['auth'])->group(function () {
            Route::get('/hr-list',    [ManualRecapController::class, 'hrList'])->name('hr-list');
            Route::get('/shift-list', [ManualRecapController::class, 'shiftList'])->name('shift-list');
            Route::post('/',          [ManualRecapController::class, 'store'])->name('store');
        });

        // Route
Route::get('manual-recap/signed-url', [ManualRecapController::class, 'signedUrl'])
    ->name('manual-recap.signedUrl');

// Controller


        Route::group(['middleware' => ['permission:ManageFingerspotRecap']], function () {

            // ── Fingerprint Recap (rekap otomatis dari DB fingerprint) ──
            Route::prefix('fingerprint-recap')->name('fingerprint-recap.')->middleware(['auth'])->group(function () {
                Route::get('/',       [FingerprintRecapController::class, 'index'])->name('index');
                // Route::post('/data',  [FingerprintRecapController::class, 'getData'])->name('data');
                Route::match(['GET', 'POST'], '/data', [FingerprintRecapController::class, 'getData'])->name('data');

                Route::post('/recap', [FingerprintRecapController::class, 'recap'])->name('recap');
                Route::get('/fingerprint-recap/export', [FingerprintRecapController::class, 'export'])
                    ->name('export');
            });
        });
    });
    Route::group(['middleware' => ['permission:ManageShifts']], function () {
        Route::post('/shifts', [ShiftsController::class, 'store'])->name('shifts.store');
        Route::put('/shifts/{id}', [ShiftsController::class, 'update'])->name('shifts.update');
        Route::delete('/shifts/{id}', [ShiftsController::class, 'destroy'])->name('shifts.destroy');
    });

    // ── Shifts ──
    Route::group(['middleware' => ['permission:ManageShifts|ManageShiftsSPVManager|ViewShifts']], function () {
        Route::get('/Shifts', [ShiftsController::class, 'index'])->name('pages.Shifts');
        Route::match(['GET', 'POST'], '/shifts/data', [ShiftsController::class, 'getData'])->name('shifts.data');
    });

    // ── Position ──
    Route::group(['middleware' => ['permission:ManagePositions']], function () {
        Route::get('/Position', [PositionController::class, 'index'])
            ->name('pages.Position');
        Route::get('Position/create', [PositionController::class, 'create'])->name('Position.create');
        Route::post('/Position', [PositionController::class, 'store'])->name('Position.store');
        Route::get('/Position/edit/{hashedId}', [PositionController::class, 'edit'])->name('Position.edit');
        Route::put('/Position/{hashedId}', [PositionController::class, 'update'])->name('Position.update');
        Route::get('/positions/positions', [PositionController::class, 'getPositions'])->name('positions.positions');
    });
    Route::group(['middleware' => ['permission:ManageAttendanceMobile']], function () {
        Route::get('/AttendanceMobile', [AttendanceMobileController::class, 'index'])
            ->name('pages.AttendanceMobile');
        Route::get('/AttendanceMobile/show/{hashedId}', [AttendanceMobileController::class, 'show'])->name('attendancemobile.show');
           Route::get('/attendancemobiles/attendancemobiles', [AttendanceMobileController::class, 'getAttendanceMobiles'])->name('attendancemobiles.attendancemobiles');
    });

    Route::group(['middleware' => ['permission:ManageAssetcategories']], function () {
        Route::get('/AssetCategories', [AssetCategoriesController::class, 'index'])
            ->name('pages.AssetCategories');
        Route::get('AssetCategories/create', [AssetCategoriesController::class, 'create'])->name('AssetCategories.create');
        Route::post('/AssetCategories', [AssetCategoriesController::class, 'store'])->name('AssetCategories.store');
        Route::get('/AssetCategories/edit/{id}', [AssetCategoriesController::class, 'edit'])->name('AssetCategories.edit');
        Route::put('/AssetCategories/{id}', [AssetCategoriesController::class, 'update'])->name('AssetCategories.update');
        Route::get('/assetcategories/assetcategories', [AssetCategoriesController::class, 'getAssetCategories'])->name('assetcategories.assetcategories');
    });
    Route::group(['middleware' => ['permission:ManageAssets']], function () {
        Route::get('/Assets', [AssetsController::class, 'index'])
            ->name('pages.Assets');
        Route::get('Assets/create', [AssetsController::class, 'create'])->name('Assets.create');
        Route::post('/Assets', [AssetsController::class, 'store'])->name('Assets.store');
        Route::get('/Assets/edit/{id}', [AssetsController::class, 'edit'])->name('Assets.edit');
        Route::put('/Assets/{id}', [AssetsController::class, 'update'])->name('Assets.update');
        Route::get('/assets/assets', [AssetsController::class, 'getAssets'])->name('assets.assets');
    });

    // ── Sktemplates ──
    Route::group(['middleware' => ['permission:ManageSktemplates']], function () {
        Route::get('/Sktemplate', [SktemplateController::class, 'sktemplate'])
            ->name('pages.Sktemplate');
        Route::get('Sktemplate/create', [SktemplateController::class, 'create'])->name('Sktemplate.create');
        Route::post('/Sktemplate', [SktemplateController::class, 'store'])->name('Sktemplate.store');
        Route::get('/Sktemplate/edit/{hashedId}', [SktemplateController::class, 'edit'])->name('Sktemplate.edit');
        Route::put('/Sktemplate/{hashedId}', [SktemplateController::class, 'update'])->name('Sktemplate.update');
        Route::get('/sktemplates/sktemplates', [SktemplateController::class, 'getSktemplates'])->name('sktemplates.sktemplates');
    });

    // ── Master Submissions ──
    Route::group(['middleware' => ['permission:ManageMasterSubmissions']], function () {
        Route::get('/MasterSubmission', [MasterSubmissionController::class, 'index'])
            ->name('pages.MasterSubmission');
        Route::get('MasterSubmission/create', [MasterSubmissionController::class, 'create'])->name('MasterSubmission.create');
        Route::post('/MasterSubmission', [MasterSubmissionController::class, 'store'])->name('MasterSubmission.store');
        Route::get('/MasterSubmission/edit/{hashedId}', [MasterSubmissionController::class, 'edit'])->name('MasterSubmission.edit');
        Route::put('/MasterSubmission/{hashedId}', [MasterSubmissionController::class, 'update'])->name('MasterSubmission.update');
        Route::get('/mastersubmissions/mastersubmissions', [MasterSubmissionController::class, 'getMasterSubmissions'])->name('mastersubmissions.mastersubmissions');
    });

    // ── Leaves Type ──
    Route::group(['middleware' => ['permission:ManageLeavestype']], function () {
        Route::get('/Leavestype', [LeavetypesController::class, 'index'])
            ->name('pages.Leavestype');
        Route::get('Leavestype/create', [LeavetypesController::class, 'create'])->name('Leavestype.create');
        Route::post('/Leavestype', [LeavetypesController::class, 'store'])->name('Leavestype.store');
        Route::get('/Leavestype/edit/{hashedId}', [LeavetypesController::class, 'edit'])->name('Leavestype.edit');
        Route::put('/Leavestype/{hashedId}', [LeavetypesController::class, 'update'])->name('Leavestype.update');
        Route::get('/leavestypes/leavestypes', [LeavetypesController::class, 'getLeavestypes'])->name('leavestypes.leavestypes');
    });

    // ── Leaves Balance ──
    Route::group(['middleware' => ['permission:ManageLeavesbalance']], function () {
        Route::get('/Leavesbalance', [LeavebalancesController::class, 'index'])
            ->name('pages.Leavesbalance');
        Route::get('/leavesbalances/leavesbalances', [LeavebalancesController::class, 'getLeavesbalances'])->name('leavesbalances.leavesbalances');
        Route::get('/Leavesbalance/edit/{hashedId}', [LeavebalancesController::class, 'edit'])->name('Leavesbalance.edit');
        Route::put('/Leavesbalance/{hashedId}', [LeavebalancesController::class, 'update'])->name('Leavesbalance.update');
    });

    // ── Leave Request ──
    Route::group(['middleware' => ['permission:ManageLeaverequest']], function () {
        Route::get('/Leaverequest', [LeaverequestController::class, 'index'])
            ->name('pages.Leaverequest');
        Route::get('Leaverequest/create', [LeaverequestController::class, 'create'])->name('Leaverequest.create');
        Route::get('/Leaverequest/edit/{hashedId}', [LeaverequestController::class, 'edit'])->name('Leaverequest.edit');
        Route::put('/Leaverequest/{hashedId}', [LeaverequestController::class, 'update'])->name('Leaverequest.update');
        Route::get('/leaverequests/leaverequests', [LeaverequestController::class, 'getLeaverequests'])->name('leaverequests.leaverequests');
    });

    // ── Department ──
    Route::group(['middleware' => ['permission:ManageDepartments']], function () {
        Route::get('/Department', [DepartmentController::class, 'index'])
            ->name('pages.Department');
        Route::get('Department/create', [DepartmentController::class, 'create'])->name('Department.create');
        Route::post('/Department', [DepartmentController::class, 'store'])->name('Department.store');
        Route::get('/Department/edit/{hashedId}', [DepartmentController::class, 'edit'])->name('Department.edit');
        Route::put('/Department/{hashedId}', [DepartmentController::class, 'update'])->name('Department.update');
        Route::match(['GET', 'POST'], '/departments/departments', [DepartmentController::class, 'getDepartments'])->name('departments.departments');
    });
    Route::group(['middleware' => ['permission:ManageDocument']], function () {
        Route::get('/document', [DocumentController::class, 'index'])
            ->name('document.index');
        Route::match(['GET', 'POST'], '/documents/documents', [DocumentController::class, 'getDocuments'])->name('documents.documents');
        Route::get(
            '/documents/download/{document}',
            [DocumentController::class, 'downloadDocument']
        )->name('documents.download');
    });

    // ── Public Holidays ──
    Route::group(['middleware' => ['permission:ManagePH|ManagePHSPVManager|ViewPH']], function () {
        Route::get('/Pubholi', [PHController::class, 'index'])
            ->name('pages.Pubholi');

        Route::get('/pubholis/pubholis', [PHController::class, 'getPubholidays'])->name('pubholis.pubholis');
    });
    Route::group(['middleware' => ['permission:ManagePH']], function () {
        Route::get('Pubholi/create', [PHController::class, 'create'])->name('Pubholi.create');
        Route::post('/Pubholi', [PHController::class, 'store'])->name('Pubholi.store');
        Route::get('/Pubholi/edit/{hashedId}', [PHController::class, 'edit'])->name('Pubholi.edit');
        Route::put('/Pubholi/{hashedId}', [PHController::class, 'update'])->name('Pubholi.update');
        Route::get('/ImportPH', [PHController::class, 'indexphs'])
            ->name('pages.ImportPH');
        Route::post('/ImportPH', [PHController::class, 'Importphs'])->name('ImportPH.phs');
        Route::get('/ImportPH/downloadphs/{filename}', [PHController::class, 'downloadphs'])->name('ImportPH.downloadphs');
    });



    // ── Summaries ──
    Route::group(['middleware' => ['permission:ManageSummaries']], function () {
        Route::get('/Summaries', [SummaryController::class, 'index'])
            ->name('pages.Summaries');
        Route::get('/summaries/summaries', [SummaryController::class, 'getSummaries'])->name('summaries.summaries');
    });

    // ── Grading List ──
    Route::group(['middleware' => ['permission:ManageGradinglist']], function () {
        Route::get('/Gradinglist', [GradinglistController::class, 'index'])
            ->name('pages.Gradinglist');
        Route::get('/Gradinglist/edit/{hashedId}', [GradinglistController::class, 'edit'])->name('Gradinglist.edit');
        Route::put('/Gradinglist/{hashedId}', [GradinglistController::class, 'update'])->name('Gradinglist.update');
        Route::get('/gradinglists/gradinglists', [GradinglistController::class, 'getGradinglists'])->name('gradinglists.gradinglists');
    });

    // ── Grading ──
    Route::group(['middleware' => ['permission:ManageGrading']], function () {
        Route::get('/Grading', [GradingController::class, 'index'])
            ->name('pages.Grading');
        Route::get('Grading/create', [GradingController::class, 'create'])->name('Grading.create');
        Route::post('/Grading', [GradingController::class, 'store'])->name('Grading.store');
        Route::get('/Grading/edit/{hashedId}', [GradingController::class, 'edit'])->name('Grading.edit');
        Route::put('/Grading/{hashedId}', [GradingController::class, 'update'])->name('Grading.update');
        Route::get('/gradings/gradings', [GradingController::class, 'getGradings'])->name('gradings.gradings');
    });

    // ── Stores ──
    Route::group(['middleware' => ['permission:ManageStores']], function () {
        Route::get('/Store', [StoreController::class, 'index'])
            ->name('pages.Store');
        Route::get('Store/create', [StoreController::class, 'create'])->name('Store.create');
        Route::post('/Store', [StoreController::class, 'store'])->name('Store.store');
        Route::get('/Store/edit/{hashedId}', [StoreController::class, 'edit'])->name('Store.edit');
        Route::put('/Store/{hashedId}', [StoreController::class, 'update'])->name('Store.update');
        Route::get('/stores/stores', [StoreController::class, 'getStores'])->name('stores.stores');
    });

    // ── Banks ──
    Route::group(['middleware' => ['permission:ManageBanks']], function () {
        Route::get('/Banks', [BanksController::class, 'index'])
            ->name('pages.Banks');
        Route::get('Banks/create', [BanksController::class, 'create'])->name('Banks.create');
        Route::post('/Banks', [BanksController::class, 'store'])->name('Banks.store');
        Route::get('/Banks/edit/{hashedId}', [BanksController::class, 'edit'])->name('Banks.edit');
        Route::put('/Banks/{hashedId}', [BanksController::class, 'update'])->name('Banks.update');
        Route::get('/banks/banks', [BanksController::class, 'getBanks'])->name('banks.banks');
    });

    // ── Companies ──
    Route::group(['middleware' => ['permission:ManageCompanies']], function () {
        Route::get('/Company', [CompanyController::class, 'index'])
            ->name('pages.Company');
        Route::get('Company/create', [CompanyController::class, 'create'])->name('Company.create');
        Route::post('/Company', [CompanyController::class, 'store'])->name('Company.store');
        Route::get('/Company/edit/{hashedId}', [CompanyController::class, 'edit'])->name('Company.edit');
        Route::put('/Company/{hashedId}', [CompanyController::class, 'update'])->name('Company.update');
        Route::get('/company/company', [CompanyController::class, 'getCompanys'])->name('company.company');
    });
    // ── Dashboard Human ──
    Route::group(['middleware' => ['auth', 'permission:dashboardHuman']], function () {
        Route::get('/dashboardHuman', [DashboardHumanController::class, 'index'])
            ->name('pages.dashboardHuman');
    });

    // ── Dashboard Manager & Team ──
    Route::group(['middleware' => ['auth', 'permission:dashboardManager']], function () {
        Route::get('/dashboardTeam', [DashManagerController::class, 'indexteam'])
            ->name('pages.dashboardTeam');
        Route::get('/dashboardManager', [DashManagerController::class, 'index'])
            ->name('pages.dashboardManager');
        Route::post('/leaverequest/{id}/approve', [LeaverequestController::class, 'approve'])->name('leaverequest.approve');
        Route::post('/leaverequest/{id}/reject',  [LeaverequestController::class, 'reject'])->name('leaverequest.reject');
        Route::get('/leaverequest',              [LeaverequestController::class, 'index'])->name('leaverequest.index');
    });
    Route::group(['middleware' => ['auth', 'permission:ManageTeam']], function () {

        Route::get('/Team', [DashManagerController::class, 'team'])
            ->name('pages.Team');
        Route::get('/Team/show/{hashedId}', [DashManagerController::class, 'show'])->name('Team.show');
        Route::get('/teams/teams', [DashManagerController::class, 'getTeams'])->name('teams.teams');
        Route::get('/orgchartteam/orgchartteam', [DashManagerController::class, 'getOrgChartDataTeam'])->name('orgchartteam.orgchartteam');
    });


    // ── Dashboard Director ──
    Route::group(['middleware' => ['auth', 'permission:dashboardDirector']], function () {
        Route::get('/dashboardDirector', [DashboardHeadController::class, 'index'])
            ->name('pages.dashboardDirector');
    });

    // ── Position Approval ──
    Route::group(['middleware' => ['auth', 'permission:Positionapprovals']], function () {
        Route::get('/PositionApproval', [PositionapprovalController::class, 'index'])->name('pages.PositionApproval');
        Route::get('/positionapprovals/positionapprovals', [PositionapprovalController::class, 'getPositionapprovals'])->name('positionapprovals.positionapprovals');
        Route::get('PositionApproval/create', [PositionapprovalController::class, 'create'])->name('PositionApproval.create');
        Route::post('/PositionApproval', [PositionapprovalController::class, 'store'])->name('PositionApproval.store');
        Route::get('/PositionApproval/edit/{hashedId}', [PositionapprovalController::class, 'edit'])->name('PositionApproval.edit');
        Route::get('/PositionApproval/show/{hashedId}', [PositionapprovalController::class, 'show'])->name('PositionApproval.show');
        Route::put('/PositionApproval/{hashedId}', [PositionapprovalController::class, 'update'])->name('PositionApproval.update');
    });

    // ── Position Request ──
    Route::group(['middleware' => ['auth', 'permission:RequestPosition']], function () {
        Route::get('/Positionrequest', [StructureSubmissionController::class, 'index'])
            ->name('pages.Positionrequest');
        Route::get('Positionrequest/create', [StructureSubmissionController::class, 'create'])->name('Positionrequest.create');
        Route::post('/Positionrequest', [StructureSubmissionController::class, 'store'])->name('Positionrequest.store');
        Route::get('/Positionrequest/edit/{hashedId}', [StructureSubmissionController::class, 'edit'])->name('Positionrequest.edit');
        Route::get('/Positionrequest/show/{hashedId}', [StructureSubmissionController::class, 'show'])->name('Positionrequest.show');
        Route::put('/Positionrequest/{hashedId}', [StructureSubmissionController::class, 'update'])->name('Positionrequest.update');
        Route::get('/positionrequests/positionrequests', [StructureSubmissionController::class, 'getPositionrequests'])->name('positionrequests.positionrequests');
    });

    // ── Salary ──
    Route::group(['middleware' => ['auth', 'permission:Salary']], function () {
        Route::get('/Salary', [SalaryController::class, 'index'])
            ->name('pages.Salary');
        Route::get('Salary/create', [SalaryController::class, 'create'])->name('Salary.create');
        Route::post('/Salary', [SalaryController::class, 'store'])->name('Salary.store');
        Route::get('/Salary/edit/{hashedId}', [SalaryController::class, 'edit'])->name('Salary.edit');
        Route::get('/Salary/show/{hashedId}', [SalaryController::class, 'show'])->name('Salary.show');
        Route::put('/SalarySalary/{hashedId}', [SalaryController::class, 'update'])->name('Salary.update');
        Route::get('/salaries/salaries', [SalaryController::class, 'getSalaries'])->name('salaries.salaries');
    });

    // ── Payroll Components ──
    Route::group(['middleware' => ['auth', 'permission:PayrollComponents']], function () {
        Route::get('/payrollcomponents', [PayrollcomponentsController::class, 'index'])
            ->name('payrollcomponents');
        Route::get('payrollcomponents/create', [PayrollcomponentsController::class, 'create'])->name('payrollcomponents.create');
        Route::post('/payrollcomponents', [PayrollcomponentsController::class, 'store'])->name('payrollcomponents.store');
        Route::get('/payrollcomponents/edit/{id}', [PayrollcomponentsController::class, 'edit'])->name('editpayrollcomponents');
        Route::put('/payrollcomponents/{id}', [PayrollcomponentsController::class, 'update'])->name('updatepayrollcomponents');
        Route::get('/payrollcomponents/payrollcomponents', [PayrollcomponentsController::class, 'getPayrollcomponents'])->name('payrollcomponents.payrollcomponents');
    });
    Route::prefix('employee-salary')->name('employeesalary.')->group(function () {
        Route::group(['middleware' => ['auth', 'permission:ManageEmployeeSalary']], function () {
            Route::get('/',             [EmployeeSalaryController::class, 'index'])->name('index');
            Route::get('/data',         [EmployeeSalaryController::class, 'getEmployeeSalaries'])->name('data');
            Route::get('/activity', [EmployeeSalaryController::class, 'getActivitySalary'])->name('activity');
            Route::get('/create',       [EmployeeSalaryController::class, 'create'])->name('create');
            Route::get('/export', [EmployeeSalaryController::class, 'export'])->name('export');
            Route::get('/template',     [EmployeeSalaryController::class, 'downloadTemplate'])->name('template'); // ← tambah ini
            Route::post('/',            [EmployeeSalaryController::class, 'store'])->name('store');
            Route::post('/import',      [EmployeeSalaryController::class, 'import'])->name('import');
            Route::get('/{id}/edit',    [EmployeeSalaryController::class, 'edit'])->name('edit');
            Route::put('/{id}',         [EmployeeSalaryController::class, 'update'])->name('update');
        });
    });

    Route::prefix('payroll-period')->name('payrollperiod.')->group(function () {
        Route::group(['middleware' => ['auth', 'permission:ManagePayrollPeriod']], function () {
            Route::get('/',              [PayrollPeriodController::class, 'index'])->name('index');
            Route::get('/data',          [PayrollPeriodController::class, 'getPayrollPeriod'])->name('data');
            Route::post('/',             [PayrollPeriodController::class, 'store'])->name('store');
            Route::get('/{id}/close',    [PayrollPeriodController::class, 'close'])->name('close');
            Route::get('/{id}/lock',     [PayrollPeriodController::class, 'lock'])->name('lock');
        });
    });

    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::group(['middleware' => ['auth', 'permission:ManagePayroll']], function () {
            Route::get('/{periodId}',              [PayrollController::class, 'index'])->name('index');
            Route::get('/{periodId}/data',         [PayrollController::class, 'getPayroll'])->name('data');
            Route::get('/{periodId}/generate',     [PayrollController::class, 'generate'])->name('generate');
            Route::post('/{periodId}/generate-one', [PayrollController::class, 'generateOne'])->name('generateOne');
            Route::get('/{periodId}/export', [PayrollController::class, 'export'])->name('export');
            Route::get('/{id}/show',               [PayrollController::class, 'show'])->name('show');
            Route::get('/{id}/edit',               [PayrollController::class, 'edit'])->name('edit');
            Route::put('/{id}',                    [PayrollController::class, 'update'])->name('update');
            Route::post('/{id}/approve',           [PayrollController::class, 'approve'])->name('approve');
            Route::post('/approve-bulk',           [PayrollController::class, 'approveBulk'])->name('approveBulk');
            Route::post('/{id}/paid',              [PayrollController::class, 'paid'])->name('paid');
            Route::post('/{periodId}/import-attendance',      [PayrollController::class, 'importAttendance'])->name('importAttendance');
            Route::get('/{periodId}/attendance-template',     [PayrollController::class, 'downloadAttendanceTemplate'])->name('attendanceTemplate');
            Route::delete('/{id}',           [PayrollController::class, 'destroy'])->name('destroy');
Route::post('/bulk-destroy',     [PayrollController::class, 'destroyBulk'])->name('destroyBulk');

Route::get('/{id}/slip',                 [PayrollController::class, 'downloadSlip'])->name('slip');
Route::post('/{id}/slip/send',           [PayrollController::class, 'sendSlipEmail'])->name('slipSend');
Route::post('/{periodId}/slip/send-bulk',[PayrollController::class, 'sendSlipEmailBulk'])->name('slipSendBulk');
    Route::get('/{periodId}/slip-bulk',        [PayrollController::class, 'downloadSlipBulk'])->name('slipBulk');
    Route::post('/{periodId}/slip/send-bulk',  [PayrollController::class, 'sendSlipEmailBulk'])->name('slipSendBulk');

        });
    });

    // ── Position Request List ──
    Route::group(['middleware' => ['auth', 'permission:RequestPositionList']], function () {
        Route::get('/Positionreqlist', [PositionreqController::class, 'index'])
            ->name('pages.Positionreqlist');
        Route::get('/Positionreqlist/edit/{hashedId}', [PositionreqController::class, 'edit'])->name('Positionreqlist.edit');
        Route::get('/Positionreqlist/show/{hashedId}', [PositionreqController::class, 'show'])->name('Positionreqlist.show');
        Route::put('/Positionreqlist/{hashedId}', [PositionreqController::class, 'update'])->name('Positionreqlist.update');
        Route::get('/positionreqlists/positionreqlists', [PositionreqController::class, 'getPositionreqlists'])->name('positionreqlists.positionreqlists');
        Route::get('/datarequest/datarequest', [PositionreqController::class, 'getReqactivities'])->name('datarequest.datarequest');
    });

    // ── Team Fingerprint ──
    Route::group(['middleware' => ['auth', 'permission:ManageTeamfingerprint']], function () {
        Route::get('/Teamfingerprint', [DashManagerController::class, 'indexteamfingerprint'])
            ->name('pages.Teamfingerprint');
        Route::match(['GET', 'POST'], '/teamfingerprints/teamfingerprints', [DashManagerController::class, 'getTeamfingerprints'])->name('teamfingerprints.teamfingerprints');
    });
    Route::middleware(['auth', 'permission:toil'])
        ->prefix('toil')
        ->name('toil.')
        ->group(function () {
            // ─────────────────────────────────────────────────────────
            //  KARYAWAN (semua user) — View Saldo & History
            // ─────────────────────────────────────────────────────────

            // My Balance
            Route::get('balance', [ToilController::class, 'index'])
                ->name('balance');

            Route::get('balance/data', [ToilController::class, 'getDataActive'])
                ->name('balance.data');

            // My History
            Route::get('history', [ToilController::class, 'history'])
                ->name('history');

            Route::get('history/assignments', [ToilController::class, 'getHistoryAssignments'])
                ->name('history.assignments');

            Route::get('history/leave-requests', [ToilController::class, 'getHistoryLeaveRequests'])
                ->name('history.leave-requests');

            // ─────────────────────────────────────────────────────────
            //  HR/HeadHR ONLY — All Balances Monitoring
            // ─────────────────────────────────────────────────────────
            Route::group(['middleware' => ['auth', 'permission:allbalances']], function () {

                Route::get('all-balances', [ToilController::class, 'allBalances'])
                    ->name('all-balances');

                Route::get('all-balances/data', [ToilController::class, 'getAllBalancesData'])
                    ->name('all-balances.data');
            });

            // ─────────────────────────────────────────────────────────
            //  MANAGER ONLY — Assignment Lembur & TOIL Approval
            // ─────────────────────────────────────────────────────────

            // Route::middleware(['manager.store'])->group(function () {
            Route::group(['middleware' => ['auth', 'permission:assignment']], function () {


                // ── Assignment Lembur (Manager input lembur) ──
                Route::get('assignment', [OvertimesubmissionsController::class, 'index'])
                    ->name('assignment.index');

                Route::get('assignment/data', [OvertimesubmissionsController::class, 'getData'])
                    ->name('assignment.data');

                // DashboardTeam - Assignment Lembur (Manager input lembur)
                Route::get('assignment/subordinates', [OvertimesubmissionsController::class, 'getSubordinatesList'])
                    ->name('assignment.subordinates');

                Route::post('assignment', [OvertimesubmissionsController::class, 'store'])
                    ->name('assignment.store');

                Route::put('assignment/{id}', [OvertimesubmissionsController::class, 'update'])
                    ->name('assignment.update');

                Route::delete('assignment/{id}', [OvertimesubmissionsController::class, 'destroy'])
                    ->name('assignment.destroy');


                // ── TOIL Approval (Manager input klaim cuti TOIL + langsung approved) ──
                Route::get('approval', [ToilLeaveRequestsController::class, 'approvalIndex'])
                    ->name('approval.index');

                Route::get('approval/data', [ToilLeaveRequestsController::class, 'getApprovalData'])
                    ->name('approval.data');

                Route::get('approval/saldo/{employeeId}', [ToilLeaveRequestsController::class, 'getEmployeeSaldoToil'])
                    ->name('approval.saldo');

                Route::post('approval', [ToilLeaveRequestsController::class, 'store'])
                    ->name('approval.store');

                Route::put('approval/{id}/cancel', [ToilLeaveRequestsController::class, 'cancelApproved'])
                    ->name('approval.cancel');
            });
        });


        Route::group(['middleware' => ['permission:ManageOvertimeRate']], function () {
        Route::get('/overtime-rate', [OvertimeRateController::class, 'index'])
            ->name('overtime.rate');
        Route::post('/overtime-rate', [OvertimeRateController::class, 'store'])->name('overtime-rate.store');
        Route::match(['GET', 'POST'], '/overtimerates/data', [OvertimeRateController::class, 'getOvertimeRates'])->name('overtimerates.overtimerates');
    });

    // ════════════════════════════════════════════════════════════════
    //   ROSTER & RELATED (di luar grup auth+role utama)
    // ════════════════════════════════════════════════════════════════

    Route::prefix('roster')->name('roster.')->group(function () {


        // ── Akses: Admin + SPV/Manager ──
        Route::middleware(['auth', 'permission:ManageRoster|ManageRosterSPVManager'])->group(function () {
            Route::post('/store',        [RosterController::class, 'store'])->name('store');
            Route::post('/destroy',      [RosterController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-assign',  [RosterController::class, 'bulkAssign'])->name('bulkAssign');
            Route::post('/bulk-delete',  [RosterController::class, 'bulkDelete'])->name('bulkDelete');

            Route::post('/import', [RosterController::class, 'importExcel'])->name('import');
            Route::get('/ph-carryovers', [RosterController::class, 'availablePhCarryovers'])->name('phCarryovers');

            Route::get('/template', [RosterController::class, 'downloadTemplate'])->name('template');


            Route::get('activities', [RosterController::class, 'getActivities'])->name('activities');
            Route::get('roster/sick-attachment-url', [RosterController::class, 'sickAttachmentUrl'])
                ->name('sick-attachment-url');
        });
        Route::middleware(['auth', 'permission:ManageRoster|ManageRosterSPVManager|ViewRoster'])->group(function () {
            Route::get('/',              [RosterController::class, 'index'])->name('index');
            Route::get('/history', [RosterController::class, 'history'])->name('history');
            Route::get('/history/export', [RosterController::class, 'historyExport'])->name('history.export');
        });
        Route::middleware(['auth', 'permission:ManageRoster'])->group(function () {

            Route::post('/copy',         [RosterController::class, 'copyRoster'])->name('copyRoster');
            Route::get('/auto-generate/preview',  [AutoRosterController::class, 'preview'])->name('auto-generate.preview');
            Route::post('/auto-generate',         [AutoRosterController::class, 'generate'])->name('auto-generate');
        });
    });

    // ── Akses: Admin only ──
    Route::prefix('roster/auto-generate/other')
        ->name('roster.auto-generate.other.')
        ->middleware(['auth', 'permission:ManageRoster'])
        ->group(function () {
            Route::get('stores',  [AutoRosterOtherStoreController::class, 'listStores'])->name('stores');
            Route::get('preview', [AutoRosterOtherStoreController::class, 'preview'])->name('preview');
            Route::post('/',      [AutoRosterOtherStoreController::class, 'generate'])->name('generate');
        });

    Route::group(['middleware' => ['permission:ManageSkLetters']], function () {
        Route::get('/SkLetters', [SkLetterController::class, 'index'])
            ->name('SkLetters');
        Route::get('SkLetters/create', [SkLetterController::class, 'create'])->name('SkLetters.create');
        Route::post('/SkLetters', [SkLetterController::class, 'store'])->name('SkLetters.store');
        Route::get('/SkLetters/edit/{skletter}', [SkLetterController::class, 'edit'])->name('SkLetters.edit');
        Route::get('SkLetters/show/{skletter}', [SkLetterController::class, 'show'])
            ->name('SkLetters.show');
        Route::put('/SkLetters/{skletter}', [SkLetterController::class, 'update'])->name('SkLetters.update');
        Route::get('/skletters/skletters', [SkLetterController::class, 'getSkLetters'])->name('skletters.skletters');
        Route::get('SkLetters/{skLetter}/pdf', [SkLetterController::class, 'viewPdf'])
            ->name('SkLetters.pdf');
        // gabung dengan sktype jadi 1 permission
    });
    Route::group(['middleware' => ['permission:ManageStLetters']], function () {
        Route::get('/SkLetters', [SkLetterController::class, 'index'])
            ->name('SkLetters');
        Route::get('SkLetters/create', [SkLetterController::class, 'create'])->name('SkLetters.create');
        Route::post('/SkLetters', [SkLetterController::class, 'store'])->name('SkLetters.store');
        Route::get('/SkLetters/edit/{skletter}', [SkLetterController::class, 'edit'])->name('SkLetters.edit');
        Route::get('SkLetters/show/{skletter}', [SkLetterController::class, 'show'])
            ->name('SkLetters.show');
        Route::put('/SkLetters/{skletter}', [SkLetterController::class, 'update'])->name('SkLetters.update');
        Route::get('/skletters/skletters', [SkLetterController::class, 'getSkLetters'])->name('skletters.skletters');
        Route::get('SkLetters/{skLetter}/pdf', [SkLetterController::class, 'viewPdf'])
            ->name('SkLetters.pdf');
        // gabung dengan sktype jadi 1 permission
    });
});

Route::group(['middleware' => ['permission:ManageSktypes']], function () {
    Route::get('/Sktype', [SKController::class, 'sktype'])
        ->name('pages.Sktype');
    Route::get('Sktype/create', [SKController::class, 'create'])->name('Sktype.create');
    Route::post('/Sktype', [SKController::class, 'store'])->name('Sktype.store');
    Route::get('/Sktype/edit/{hashedId}', [SKController::class, 'edit'])->name('Sktype.edit');
    Route::put('/Sktype/{hashedId}', [SKController::class, 'update'])->name('Sktype.update');
    Route::get('/sktypes/sktypes', [SKController::class, 'getSktypes'])->name('sktypes.sktypes');
});
// ════════════════════════════════════════════════════════════════
//   TOIL SYSTEM ROUTES
// ════════════════════════════════════════════════════════════════
// Route::group(['middleware' => ['auth', 'permission:toil']]->prefix('toil')->name('toil.')->group(function () {
// Route::middleware(['auth'])->prefix('toil')->name('toil.')->group(function () {
// ── Manual Recap (+Add Recap feature) ──
// ════════════════════════════════════════════════════════════════
//   GUEST ROUTES
// ════════════════════════════════════════════════════════════════
Route::group(['middleware' => 'guest'], function () {
    Route::middleware(['throttle:10,1'])->group(function () {
        Route::post('/session', [LoginController::class, 'store'])->name('session');
        Route::get('/', [LoginController::class, 'index'])->name('login');
        Route::get('Career', [CareerController::class, 'index'])->name('pages.Career');
        Route::get('About-us', [CareerController::class, 'indexabout'])->name('pages.About-us');
        Route::get('/two-factor/verify', [TwoFactorController::class, 'showVerify'])->name('2fa.verify');
Route::post('/two-factor/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.post');

    });
});
// ── Guest routes (tanpa throttle, di-define ulang sesuai original) ──
Route::group(['middleware' => 'guest'], function () {
    Route::get('Career', [CareerController::class, 'index'])->name('pages.Career');
    Route::get('About-us', [CareerController::class, 'indexabout'])->name('pages.About-us');
});
