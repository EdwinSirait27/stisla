<?php

use App\Http\Controllers\dashboardAdminController;
use App\Http\Controllers\DashboardManagerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardHRController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PayrollEmailController;
use App\Http\Controllers\EmployeeImportController;
use App\Http\Controllers\PayrollsController;
use App\Http\Controllers\UserprofileController;
use App\Http\Controllers\DashManagerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\BanksController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\FingerspotController;
use App\Http\Controllers\AttendanceimportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FingerprintsController;
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
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\LeavesController;
use App\Http\Controllers\LeavebalancesController;
use App\Http\Controllers\MasterSubmissionController;
use App\Http\Controllers\PositionapprovalController;
use App\Http\Controllers\SKController;
use App\Http\Controllers\SktemplateController;
use App\Http\Controllers\LeavetypesController;
use App\Http\Controllers\StructuresnewController;
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

Route::view('/test-wireui', 'test-wireui');
// Route::middleware(['auth', 'role:Admin|HeadHR|HR|Human|Manager|Director'])->group(function () {
//     Route::get('/feature-profile', [UserprofileController::class, 'index'])
//         ->name('pages.feature-profile');
//     Route::get('/change-password', [UserprofileController::class, 'indexpassword'])
//         ->name('pages.change-password');
//     Route::put('/feature-profile/update', [UserprofileController::class, 'updateemailtelp'])->name('feature-profile.updateemailtelp');
//     Route::put('/change-password/update', [UserprofileController::class, 'updatePassword'])->name('change-password.updatepassword');
//     Route::put('/feature-profile', [UserprofileController::class, 'index'])->name('feature-profile');
//     Route::put('/change-password', [UserprofileController::class, 'indexpassword'])->name('change-password');
//     Route::match(['GET', 'POST'], '/logout', [LoginController::class, 'destroy'])
//         ->name('logout');
// Route::get('/feature-profile', [UserprofileController::class, 'index'])
//     ->name('pages.feature-profile');
// Route::put('/feature-profile/update', [UserprofileController::class, 'updateemailtelp'])->name('feature-profile.update');
// Route::put('/feature-profile', [UserprofileController::class, 'index'])->name('feature-profile');
// Route::match(['GET', 'POST'], '/logout', [LoginController::class, 'destroy'])
//     ->name('logout');
// FEATURE PROFILE (GET)
Route::middleware(['auth', 'role:Admin|HeadHR|HR|Human|Manager|Director'])->group(function () {
    Route::get('/feature-profile', [UserprofileController::class, 'index'])
        ->name('pages.feature-profile');
    Route::get('/change-password', [UserprofileController::class, 'indexpassword'])
        ->name('pages.change-password');
    // FEATURE PROFILE UPDATE (PUT)
    Route::put('/change-password/update', [UserprofileController::class, 'updatePassword'])
        ->name('change-password.update');
    Route::put('/feature-profile/update', [UserprofileController::class, 'updateemailtelp'])
        ->name('feature-profile.update');
    // LOGOUT
    Route::match(['GET', 'POST'], '/logout', [LoginController::class, 'destroy'])
        ->name('logout');

    Route::group(['middleware' => ['permission:dashboardAdmin']], function () {
        Route::get('/dashboardAdmin', [DashboardAdminController::class, 'index'])
            ->name('pages.dashboardAdmin');
        Route::get('/dashboardAdmin/edit/{hashedId}', [dashboardAdminController::class, 'edit'])->name('dashboardAdmin.edit');
        Route::get('/dashboardAdmin/show/{hashedId}', [dashboardAdminController::class, 'show'])->name('dashboardAdmin.show');
        Route::put('/dashboardAdmin/{hashedId}', [dashboardAdminController::class, 'update'])->name('dashboardAdmin.update');
        Route::match(['GET', 'POST'], '/users/users', [dashboardAdminController::class, 'getUsers'])->name('users.users');
        Route::post('/users/bulk-update-role', [dashboardAdminController::class, 'bulkUpdateRole'])->name('users.bulkUpdateRole');
    });

    Route::match(['GET', 'POST'], '/logout', [LoginController::class, 'destroy'])
        ->name('logout');
    Route::group(['middleware' => ['permission:dashboardAdmin']], function () {
        Route::get('/dashboardAdmin', [DashboardAdminController::class, 'index'])
            ->name('pages.dashboardAdmin');
        Route::get('/dashboardAdmin/edit/{hashedId}', [dashboardAdminController::class, 'edit'])->name('dashboardAdmin.edit');
        Route::get('/dashboardAdmin/show/{hashedId}', [dashboardAdminController::class, 'show'])->name('dashboardAdmin.show');
        Route::put('/dashboardAdmin/{hashedId}', [dashboardAdminController::class, 'update'])->name('dashboardAdmin.update');
        Route::match(['GET', 'POST'], '/users/users', [dashboardAdminController::class, 'getUsers'])->name('users.users');
        Route::post('/users/bulk-update-role', [dashboardAdminController::class, 'bulkUpdateRole'])->name('users.bulkUpdateRole');
    });
    Route::group(['middleware' => ['permission:dashboardAdmin']], function () {
        Route::get('/dashboardAdmin', [DashboardAdminController::class, 'index'])
            ->name('pages.dashboardAdmin');
        Route::get('/dashboardAdmin/edit/{hashedId}', [dashboardAdminController::class, 'edit'])->name('dashboardAdmin.edit');
        Route::get('/dashboardAdmin/show/{hashedId}', [dashboardAdminController::class, 'show'])->name('dashboardAdmin.show');
        Route::put('/dashboardAdmin/{hashedId}', [dashboardAdminController::class, 'update'])->name('dashboardAdmin.update');
        Route::match(['GET', 'POST'], '/users/users', [dashboardAdminController::class, 'getUsers'])->name('users.users');
        Route::post('/users/bulk-update-role', [dashboardAdminController::class, 'bulkUpdateRole'])->name('users.bulkUpdateRole');
    });
    Route::group(['middleware' => ['permission:dashboardAdmin']], function () {
        Route::get('/Activity', [ActivityController::class, 'index'])->name('pages.Activity');
        Route::get('/Activity/show/{hashedId}', [ActivityController::class, 'show'])->name('Activity.show');
        Route::get('/activity/activity', [ActivityController::class, 'getActivity'])->name('activity.activity');
        Route::get('/activity1/activity1', [ActivityController::class, 'getActivity1'])->name('activity1.activity1');
    });
    Route::middleware(['auth'])->group(function () {
        Route::post('/Submissions', [SubmissionsController::class, 'store'])->name('Submissions.store');
    });
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
    Route::group(['middleware' => ['permission:dashboardHR']], function () {
        Route::get('/dashboardHR', [DashboardHRController::class, 'index'])
            ->name('pages.dashboardHR');
        Route::get('/dashboardHR/data', [DashboardHRController::class, 'getMonthlyData'])->name('dashboardHR.data');
        Route::get('/announcements/announcements', [DashboardHRController::class, 'getAnnouncements'])->name('announcements.announcements');
        Route::post('/dashboardHR', [DashboardHRController::class, 'store'])->name('dashboardHR.store');
    });
    Route::group(['middleware' => ['permission:ManageEmployee']], function () {
        Route::get('/data/data', [EmployeeController::class, 'getActivities'])->name('data.data');
        Route::get('/Employee', [EmployeeController::class, 'index'])
            ->name('pages.Employee');
        Route::get('Employee/create', [EmployeeController::class, 'create'])->name('Employee.create');
        Route::post('/Employee', [EmployeeController::class, 'store'])->name('Employee.store');
        Route::get('/Employee/edit/{hashedId}', [EmployeeController::class, 'edit'])->name('Employee.edit');
        Route::get('/Employee/show/{hashedId}', [EmployeeController::class, 'show'])->name('Employee.show');
        Route::put('/Employee/{hashedId}', [EmployeeController::class, 'update'])->name('Employee.update');
        Route::get('/employees/employees', [EmployeeController::class, 'getEmployees'])->name('employees.employees');
        Route::post('/employees/transfer-all-to-payroll', [EmployeeController::class, 'transferAllToPayroll'])->name('employees.transferAllToPayroll');
        Route::get('/Employeeall', [EmployeeController::class, 'indexall'])
            ->name('pages.Employeeall');
        Route::match(['GET', 'POST'], '/employeesall/employeesall', [EmployeeController::class, 'getEmployeesall'])->name('employeesall.employeesall');
        Route::get('/Import', [EmployeeImportController::class, 'index'])
            ->name('pages.Import');
        Route::post('/Import', [EmployeeImportController::class, 'import'])->name('Import.employee');
        Route::get('/Importuser', [EmployeeImportController::class, 'indexuser'])
            ->name('pages.Importuser');
        Route::post('/Importuser', [EmployeeImportController::class, 'importuser'])->name('Importuser.user');
        // Route::get('/employee/photo/{path}', [EmployeeController::class, 'getPhoto']);
        Route::get('/employee/photo/{path}', [EmployeeController::class, 'getPhoto'])
    ->where('path', '.*')
    ->middleware('auth')
    ->name('employee.photo');


    });
    Route::group(['middleware' => ['permission:ManagePayrolls']], function () {
        Route::get('/Payrolls', [PayrollsController::class, 'index'])
            ->name('pages.Payrolls');
        Route::get('/Payrolls/edit/{hashedId}', [PayrollsController::class, 'edit'])->name('Payrolls.edit');
        Route::put('/Payrolls/{hashedId}', [PayrollsController::class, 'update'])->name('Payrolls.update');
        Route::get('/payrolls/payrolls', [PayrollsController::class, 'getPayrolls'])->name('payrolls.payrolls');
        Route::get('/Payrolls/show/{hashedId}', [PayrollsController::class, 'show'])->name('Payrolls.show');
        Route::delete('/payrolls/delete-bulk', [PayrollsController::class, 'bulkDelete'])->name('payrolls.bulkDelete');
        Route::get('/email', [PayrollEmailController::class, 'index'])->name('payroll.email.index');
        Route::post('/email/send', [PayrollEmailController::class, 'send'])->name('payroll.email.send');
        Route::get('/email/preview/{payroll}', [PayrollEmailController::class, 'preview'])->name('payroll.email.preview');
        Route::get('/payrolls/{hashedId}/generate', [PayrollsController::class, 'generate'])->name('payrolls.generate');
        Route::get('/Importpayroll', [PayrollsController::class, 'indexpayrolls'])
            ->name('pages.Importpayroll');
        Route::post('/Importpayroll', [PayrollsController::class, 'Importpayrolls'])->name('Importpayroll.payrolls');
        Route::post('/Payrolls/generate-all', [PayrollsController::class, 'generateAll'])->name('Payrolls.generateAll');
        Route::get('/Payrolls/downloadpayrolls/{filename}', [PayrollsController::class, 'downloadpayrolls'])->name('Payrolls.downloadpayrolls');
        Route::get('/payroll/export', [PayrollsController::class, 'export'])
    ->name('payroll.export');
    });
    Route::group(['middleware' => ['permission:ManageFingerspot']], function () {
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
        Route::get('/Fingerprints', [FingerprintsController::class, 'index'])
            ->name('pages.Fingerprints');
        Route::match(['GET', 'POST'], '/fingerprints/fingerprints', [FingerprintsController::class, 'getFingerprints'])->name('fingerprints.fingerprints');
        Route::get('/Fingerprints/edit/{pin}}', [FingerprintsController::class, 'editFingerprint'])->name('pages.Fingerprints.edit');
        Route::put('/fingerprints/{pin}/{scan_date}', [FingerprintsController::class, 'updateFingerprint'])->name('Fingerprints.update');
        Route::get('/Fingerprints/total-hari', [FingerprintsController::class, 'getTotalHariBekerja'])->name('Fingerprints.totalHari');
        Route::get('/Editedfinger', [Editedfingerprints::class, 'index'])
            ->name('pages.Editedfinger');
        Route::match(['GET', 'POST'], '/editedfinger/editedfinger', [Editedfingerprints::class, 'getEditedfingerprints'])->name('editedfinger.editedfinger');
    });
    // Position    
    Route::group(['middleware' => ['permission:ManagePositions']], function () {
        Route::get('/Position', [PositionController::class, 'index'])
            ->name('pages.Position');
        Route::get('Position/create', [PositionController::class, 'create'])->name('Position.create');
        Route::post('/Position', [PositionController::class, 'store'])->name('Position.store');
        Route::get('/Position/edit/{hashedId}', [PositionController::class, 'edit'])->name('Position.edit');
        Route::put('/Position/{hashedId}', [PositionController::class, 'update'])->name('Position.update');
        Route::get('/positions/positions', [PositionController::class, 'getPositions'])->name('positions.positions');
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
    Route::group(['middleware' => ['permission:ManageSktemplates']], function () {
        Route::get('/Sktemplate', [SktemplateController::class, 'sktemplate'])
            ->name('pages.Sktemplate');
        Route::get('Sktemplate/create', [SktemplateController::class, 'create'])->name('Sktemplate.create');
        Route::post('/Sktemplate', [SktemplateController::class, 'store'])->name('Sktemplate.store');
        Route::get('/Sktemplate/edit/{hashedId}', [SktemplateController::class, 'edit'])->name('Sktemplate.edit');
        Route::put('/Sktemplate/{hashedId}', [SktemplateController::class, 'update'])->name('Sktemplate.update');
        Route::get('/sktemplates/sktemplates', [SktemplateController::class, 'getSktemplates'])->name('sktemplates.sktemplates');
    });
    Route::group(['middleware' => ['permission:ManageMasterSubmissions']], function () {
        Route::get('/MasterSubmission', [MasterSubmissionController::class, 'index'])
            ->name('pages.MasterSubmission');
        Route::get('MasterSubmission/create', [MasterSubmissionController::class, 'create'])->name('MasterSubmission.create');
        Route::post('/MasterSubmission', [MasterSubmissionController::class, 'store'])->name('MasterSubmission.store');
        Route::get('/MasterSubmission/edit/{hashedId}', [MasterSubmissionController::class, 'edit'])->name('MasterSubmission.edit');
        Route::put('/MasterSubmission/{hashedId}', [MasterSubmissionController::class, 'update'])->name('MasterSubmission.update');
        Route::get('/mastersubmissions/mastersubmissions', [MasterSubmissionController::class, 'getMasterSubmissions'])->name('mastersubmissions.mastersubmissions');
    });
    Route::group(['middleware' => ['permission:ManageLeavestype']], function () {
        Route::get('/Leavestype', [LeavetypesController::class, 'index'])
            ->name('pages.Leavestype');
        Route::get('Leavestype/create', [LeavetypesController::class, 'create'])->name('Leavestype.create');
        Route::post('/Leavestype', [LeavetypesController::class, 'store'])->name('Leavestype.store');
        Route::get('/Leavestype/edit/{hashedId}', [LeavetypesController::class, 'edit'])->name('Leavestype.edit');
        Route::put('/Leavestype/{hashedId}', [LeavetypesController::class, 'update'])->name('Leavestype.update');
        Route::get('/leavestypes/leavestypes', [LeavetypesController::class, 'getLeavestypes'])->name('leavestypes.leavestypes');
    });
    Route::group(['middleware' => ['permission:ManageLeavesbalance']], function () {
        Route::get('/Leavesbalance', [LeavebalancesController::class, 'index'])
            ->name('pages.Leavesbalance');
        Route::get('/leavesbalances/leavesbalances', [LeavebalancesController::class, 'getLeavesbalances'])->name('leavesbalances.leavesbalances');
        Route::get('/Leavesbalance/edit/{hashedId}', [LeavebalancesController::class, 'edit'])->name('Leavesbalance.edit');
        Route::put('/Leavesbalance/{hashedId}', [LeavebalancesController::class, 'update'])->name('Leavesbalance.update');

    });
     Route::group(['middleware' => ['permission:ManageLeaverequest']], function () {
        Route::get('/Leaverequest', [LeaverequestController::class, 'index'])
            ->name('pages.Leaverequest');
        Route::get('Leaverequest/create', [LeaverequestController::class, 'create'])->name('Leaverequest.create');
        Route::post('/Leaverequest', [LeaverequestController::class, 'store'])->name('Leaverequest.store');
        Route::get('/Leaverequest/edit/{hashedId}', [LeaverequestController::class, 'edit'])->name('Leaverequest.edit');
        Route::put('/Leaverequest/{hashedId}', [LeaverequestController::class, 'update'])->name('Leaverequest.update');
        Route::get('/leaverequests/leaverequests', [LeaverequestController::class, 'getLeaverequests'])->name('leaverequests.leaverequests');
    });
    // Department    
    Route::group(['middleware' => ['permission:ManageDepartments']], function () {
        Route::get('/Department', [DepartmentController::class, 'index'])
            ->name('pages.Department');
        Route::get('Department/create', [DepartmentController::class, 'create'])->name('Department.create');
        Route::post('/Department', [DepartmentController::class, 'store'])->name('Department.store');
        Route::get('/Department/edit/{hashedId}', [DepartmentController::class, 'edit'])->name('Department.edit');
        Route::put('/Department/{hashedId}', [DepartmentController::class, 'update'])->name('Department.update');
        Route::get('/departments/departments', [DepartmentController::class, 'getDepartments'])->name('departments.departments');
    });
    Route::group(['middleware' => ['permission:ManagePH']], function () {
        Route::get('/Pubholi', [PHController::class, 'index'])
            ->name('pages.Pubholi');
        Route::get('Pubholi/create', [PHController::class, 'create'])->name('Pubholi.create');
        Route::post('/Pubholi', [PHController::class, 'store'])->name('Pubholi.store');
        Route::get('/Pubholi/edit/{hashedId}', [PHController::class, 'edit'])->name('Pubholi.edit');
        Route::put('/Pubholi/{hashedId}', [PHController::class, 'update'])->name('Pubholi.update');
        Route::get('/pubholis/pubholis', [PHController::class, 'getPubholidays'])->name('pubholis.pubholis');
        Route::get('/ImportPH', [PHController::class, 'indexphs'])
            ->name('pages.ImportPH');
        Route::post('/ImportPH', [PHController::class, 'Importphs'])->name('ImportPH.phs');
        Route::get('/ImportPH/downloadphs/{filename}', [PHController::class, 'downloadphs'])->name('ImportPH.downloadphs');
    });
    Route::group(['middleware' => ['permission:ManageStructures']], function () {
        Route::get('/Structures', [StructureController::class, 'index'])
            ->name('pages.Structures');
        Route::get('/Structures/edit/{hashedId}', [StructureController::class, 'edit'])->name('Structures.edit');
        Route::put('/Structures/{hashedId}', [StructureController::class, 'update'])->name('Structures.update');
        Route::get('/structures/structures', [StructureController::class, 'getStructures'])->name('structures.structures');
    });
    Route::group(['middleware' => ['permission:ManageStructuresnew']], function () {
        Route::get('/Structuresnew', [StructuresnewController::class, 'index'])
            ->name('pages.Structuresnew');
        // Route::post('/Structuresnew', [StructuresnewController::class, 'store'])->name('Structuresnew.store');
        // Route::get('Structuresnew/create', [StructuresnewController::class, 'create'])->name('Structuresnew.create');
        Route::get('/Structuresnew/edit/{hashedId}', [StructuresnewController::class, 'edit'])->name('Structuresnew.edit');
        Route::get('/Structuresnew/show/{hashedId}', [StructuresnewController::class, 'show'])->name('Structuresnew.show');
        Route::put('/Structuresnew/{hashedId}', [StructuresnewController::class, 'update'])->name('Structuresnew.update');
        Route::get('/structuresnew/structuresnew', [StructuresnewController::class, 'getStructuresnew'])->name('structuresnew.structuresnew');
        Route::get('/submissionsreq/submissionsreq', [StructuresnewController::class, 'getPositionreqs'])->name('submissionsreq.submissionsreq');
        Route::get('/orgchart/orgchart', [StructuresnewController::class, 'getOrgChartData'])->name('orgchart.orgchart');
        Route::delete('/structures/delete-bulk', [StructuresnewController::class, 'bulkDelete'])->name('structuresnew.bulkDelete');
        Route::get('/structuresnew/available-positions', [StructuresnewController::class, 'getAvailablePositions'])
            ->name('Structuresnew.availablePositions');
        Route::get('/Structuresnew/see/{idHashed}', [StructuresnewController::class, 'see'])->name('Structurenew.see');
        // Route untuk proses store ke Structuresnew
        Route::post('/store-to-structure/{hashedId}', [StructuresnewController::class, 'storeToStructure'])
            ->name('store.to.structure');
        Route::get('/datastructures/datastructures', [StructuresnewController::class, 'getStructuresativities'])->name('datastructures.datastructures');
    });
    Route::group(['middleware' => ['permission:ManageSummaries']], function () {
        Route::get('/Summaries', [SummaryController::class, 'index'])
            ->name('pages.Summaries');
        Route::get('/summaries/summaries', [SummaryController::class, 'getSummaries'])->name('summaries.summaries');
    });
    Route::group(['middleware' => ['permission:ManageGradinglist']], function () {
        Route::get('/Gradinglist', [GradinglistController::class, 'index'])
            ->name('pages.Gradinglist');
        Route::get('/Gradinglist/edit/{hashedId}', [GradinglistController::class, 'edit'])->name('Gradinglist.edit');
        Route::put('/Gradinglist/{hashedId}', [GradinglistController::class, 'update'])->name('Gradinglist.update');
        Route::get('/gradinglists/gradinglists', [GradinglistController::class, 'getGradinglists'])->name('gradinglists.gradinglists');
    });
    Route::group(['middleware' => ['permission:ManageGrading']], function () {
        Route::get('/Grading', [GradingController::class, 'index'])
            ->name('pages.Grading');
             Route::get('Grading/create', [GradingController::class, 'create'])->name('Grading.create');
        Route::post('/Grading', [GradingController::class, 'store'])->name('Grading.store');
        Route::get('/Grading/edit/{hashedId}', [GradingController::class, 'edit'])->name('Grading.edit');
        Route::put('/Grading/{hashedId}', [GradingController::class, 'update'])->name('Grading.update');
        Route::get('/gradings/gradings', [GradingController::class, 'getGradings'])->name('gradings.gradings');
    });
    Route::group(['middleware' => ['permission:ManageStores']], function () {

        Route::get('/Store', [StoreController::class, 'index'])
            ->name('pages.Store');
        Route::get('Store/create', [StoreController::class, 'create'])->name('Store.create');
        Route::post('/Store', [StoreController::class, 'store'])->name('Store.store');
        Route::get('/Store/edit/{hashedId}', [StoreController::class, 'edit'])->name('Store.edit');
        Route::put('/Store/{hashedId}', [StoreController::class, 'update'])->name('Store.update');
        Route::get('/stores/stores', [StoreController::class, 'getStores'])->name('stores.stores');
    });
    Route::group(['middleware' => ['permission:ManageBanks']], function () {

        Route::get('/Banks', [BanksController::class, 'index'])
            ->name('pages.Banks');
        Route::get('Banks/create', [BanksController::class, 'create'])->name('Banks.create');
        Route::post('/Banks', [BanksController::class, 'store'])->name('Banks.store');
        Route::get('/Banks/edit/{hashedId}', [BanksController::class, 'edit'])->name('Banks.edit');
        Route::put('/Banks/{hashedId}', [BanksController::class, 'update'])->name('Banks.update');
        Route::get('/banks/banks', [BanksController::class, 'getBanks'])->name('banks.banks');
    });
    Route::group(['middleware' => ['permission:ManageCompanies']], function () {
        Route::get('/Company', [CompanyController::class, 'index'])
            ->name('pages.Company');
        Route::get('Company/create', [CompanyController::class, 'create'])->name('Company.create');
        Route::post('/Company', [CompanyController::class, 'store'])->name('Company.store');
        Route::get('/Company/edit/{hashedId}', [CompanyController::class, 'edit'])->name('Company.edit');
        Route::put('/Company/{hashedId}', [CompanyController::class, 'update'])->name('Company.update');
        Route::get('/company/company', [CompanyController::class, 'getCompanys'])->name('company.company');
    });
    Route::group(['middleware' => ['auth', 'permission:dashboardHuman']], function () {
    Route::get('/Dashboard', [DashboardController::class, 'index'])
        ->name('pages.Dashboard.Dashboard');
});
Route::group(['middleware' => ['auth', 'permission:dashboardManager']], function () {
    Route::get('/dashboardTeam', [DashManagerController::class, 'indexteam'])
        ->name('pages.dashboardTeam');
    Route::get('/dashboardManager', [DashManagerController::class, 'index'])
        ->name('pages.dashboardManager');
    Route::get('/Team', [DashManagerController::class, 'team'])
        ->name('pages.Team');
    Route::get('/Team/show/{hashedId}', [DashManagerController::class, 'show'])->name('Team.show');
    Route::get('/teams/teams', [DashManagerController::class, 'getTeams'])->name('teams.teams');
    Route::get('/orgchartteam/orgchartteam', [DashManagerController::class, 'getOrgChartDataTeam'])->name('orgchartteam.orgchartteam');
});
Route::group(['middleware' => ['auth', 'permission:dashboardDirector']], function () {
    Route::get('/dashboardDirector', [DashboardHeadController::class, 'index'])
        ->name('pages.dashboardDirector');
});
Route::group(['middleware' => ['auth', 'permission:Positionapprovals']], function () {
    Route::get('/PositionApproval', [PositionapprovalController::class, 'index'])->name('pages.PositionApproval');
    Route::get('/positionapprovals/positionapprovals', [PositionapprovalController::class, 'getPositionapprovals'])->name('positionapprovals.positionapprovals');
    Route::get('PositionApproval/create', [PositionapprovalController::class, 'create'])->name('PositionApproval.create');
    Route::post('/PositionApproval', [PositionapprovalController::class, 'store'])->name('PositionApproval.store');
    Route::get('/PositionApproval/edit/{hashedId}', [PositionapprovalController::class, 'edit'])->name('PositionApproval.edit');
    Route::get('/PositionApproval/show/{hashedId}', [PositionapprovalController::class, 'show'])->name('PositionApproval.show');
    Route::put('/PositionApproval/{hashedId}', [PositionapprovalController::class, 'update'])->name('PositionApproval.update');
    // Route::get('/Positionapprovals/show/{hashedId}', [PositionapprovalController::class, 'show'])->name('Positionrequest.show');

});
Route::group(['middleware' => ['auth', 'permission:RequestPosition']], function () {
    Route::get('/Positionrequest', [StructureSubmissionController::class, 'index'])
        ->name('pages.Positionrequest');
    Route::get('Positionrequest/create', [StructureSubmissionController::class, 'create'])->name('Positionrequest.create');
    Route::post('/Positionrequest', [StructureSubmissionController::class, 'store'])->name('Positionrequest.store');
    Route::get('/Positionrequest/edit/{hashedId}', [StructureSubmissionController::class, 'edit'])->name('Positionrequest.edit');
    Route::get('/Positionrequest/show/{hashedId}', [StructureSubmissionController::class, 'show'])->name('Positionrequest.show');
    Route::put('/Positionrequest/{hashedId}', [StructureSubmissionController::class, 'update'])->name('Positionrequest.update');
    // Route::get('/Positionrequest/show/{hashedId}', [StructureSubmissionController::class, 'show'])->name('Positionrequest.show');

    Route::get('/positionrequests/positionrequests', [StructureSubmissionController::class, 'getPositionrequests'])->name('positionrequests.positionrequests');
});
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
Route::group(['middleware' => ['auth', 'permission:RequestPositionList']], function () {
    Route::get('/Positionreqlist', [PositionreqController::class, 'index'])
        ->name('pages.Positionreqlist');
    Route::get('/Positionreqlist/edit/{hashedId}', [PositionreqController::class, 'edit'])->name('Positionreqlist.edit');
    Route::get('/Positionreqlist/show/{hashedId}', [PositionreqController::class, 'show'])->name('Positionreqlist.show');
    Route::put('/Positionreqlist/{hashedId}', [PositionreqController::class, 'update'])->name('Positionreqlist.update');
    Route::get('/positionreqlists/positionreqlists', [PositionreqController::class, 'getPositionreqlists'])->name('positionreqlists.positionreqlists');
    Route::get('/datarequest/datarequest', [PositionreqController::class, 'getReqactivities'])->name('datarequest.datarequest');
});

Route::group(['middleware' => ['auth', 'permission:ManageTeamfingerprint']], function () {

    Route::get('/Teamfingerprint', [DashManagerController::class, 'indexteamfingerprint'])
        ->name('pages.Teamfingerprint');
    Route::match(['GET', 'POST'], '/teamfingerprints/teamfingerprints', [DashManagerController::class, 'getTeamfingerprints'])->name('teamfingerprints.teamfingerprints');
});
// Route::group(['middleware' => ['permission:ManageGroups']], function () {
//         Route::get('/Group', [GroupController::class, 'index'])
//             ->name('pages.Group');
//         Route::get('Group/create', [GroupController::class, 'create'])->name('Group.create');
//         Route::post('/Group', [GroupController::class, 'store'])->name('Group.store');
//         Route::get('/Group/edit/{hashedId}', [GroupController::class, 'edit'])->name('Group.edit');
//         Route::put('/Group/{hashedId}', [GroupController::class, 'update'])->name('Group.update');
//         Route::get('/groups/groups', [GroupController::class, 'getGroups'])->name('groups.groups');
//     });
});
Route::group(['middleware' => 'guest'], function () {
    Route::middleware(['throttle:10,1'])->group(function () {
        Route::post('/session', [LoginController::class, 'store'])->name('session');
        Route::get('/', [LoginController::class, 'index'])->name('login');
        Route::get('Career', [CareerController::class, 'index'])->name('pages.Career');
        Route::get('About-us', [CareerController::class, 'indexabout'])->name('pages.About-us');
    });
});
Route::group(['middleware' => 'guest'], function () {
    Route::get('Career', [CareerController::class, 'index'])->name('pages.Career');
    Route::get('About-us', [CareerController::class, 'indexabout'])->name('pages.About-us');
});
// Route::get('/portofolio', function () {
        //     return view('pages.portofolio');
        // });
// uoms
    // Route::group(['middleware' => ['permission:ManageUoms']], function () {

    //     Route::get('/Uoms', [UomsController::class, 'index'])
    //         ->name('pages.Uoms');
    //     Route::get('Uoms/create', [UomsController::class, 'create'])->name('Uoms.create');
    //     Route::post('/Uoms', [UomsController::class, 'store'])->name('Uoms.store');
    //     Route::get('/Uoms/edit/{hashedId}', [UomsController::class, 'edit'])->name('Uoms.edit');
    //     Route::put('/Uoms/{hashedId}', [UomsController::class, 'update'])->name('Uoms.update');
    //     Route::get('/uoms/uoms', [UomsController::class, 'getUoms'])->name('uoms.uoms');
    // });
    // // Brands
    // Route::group(['middleware' => ['permission:ManageBrands']], function () {

    //     Route::get('/Brands', [BrandsController::class, 'index'])
    //         ->name('pages.Brands');
    //     Route::get('Brands/create', [BrandsController::class, 'create'])->name('Brands.create');
    //     Route::post('/Brands', [BrandsController::class, 'store'])->name('Brands.store');
    //     Route::get('/Brands/edit/{hashedId}', [BrandsController::class, 'edit'])->name('Brands.edit');
    //     Route::put('/Brands/{hashedId}', [BrandsController::class, 'update'])->name('Brands.update');
    //     Route::get('/brands/brands', [BrandsController::class, 'getBrands'])->name('brands.brands');
    // });
    // // Categories
    // Route::group(['middleware' => ['permission:ManageCategories']], function () {

    //     Route::get('/Categories', [CategoriesController::class, 'index'])
    //         ->name('pages.Categories');
    //     Route::get('Categories/create', [CategoriesController::class, 'create'])->name('Categories.create');
    //     Route::post('/Categories', [CategoriesController::class, 'store'])->name('Categories.store');
    //     // Route::get('/Categories/edit/{hashedId}', [CategoriesController::class, 'edit'])->name('Categories.edit');
    //     // Route::put('/Categories/{hashedId}', [CategoriesController::class, 'update'])->name('Categories.update');
    //     Route::get('/categories/categories', [CategoriesController::class, 'getCategories'])->name('categories.categories');
    //     Route::get('categories/tree', [CategoriesController::class, 'getCategoryTree'])->name('categories.tree');
    // });

    // // Tax status
    // Route::group(['middleware' => ['permission:ManageTaxstatus']], function () {

    //     Route::get('/Taxstatus', [TaxstatusController::class, 'index'])
    //         ->name('pages.Taxstatus');
    //     Route::get('Taxstatus/create', [TaxstatusController::class, 'create'])->name('Taxstatus.create');
    //     Route::post('/Taxstatus', [TaxstatusController::class, 'store'])->name('Taxstatus.store');
    //     Route::get('/Taxstatus/edit/{hashedId}', [TaxstatusController::class, 'edit'])->name('Taxstatus.edit');
    //     Route::put('/Taxstatus/{hashedId}', [TaxstatusController::class, 'update'])->name('Taxstatus.update');
    //     Route::get('/taxstatus/taxstatus', [TaxstatusController::class, 'getTaxstatuses'])->name('taxstatus.taxstatus');
    // });

    // // Status Product
    // Route::group(['middleware' => ['permission:ManageStatusproduct']], function () {

    //     Route::get('/Statusproduct', [StatusproductController::class, 'index'])
    //         ->name('pages.Statusproduct');
    //     Route::get('Statusproduct/create', [StatusproductController::class, 'create'])->name('Statusproduct.create');
    //     Route::post('/Statusproduct', [StatusproductController::class, 'store'])->name('Statusproduct.store');
    //     Route::get('/Statusproduct/edit/{hashedId}', [StatusproductController::class, 'edit'])->name('Statusproduct.edit');
    //     Route::put('/Statusproduct/{hashedId}', [StatusproductController::class, 'update'])->name('Statusproduct.update');
    //     Route::get('/statusproduct/statusproduct', [StatusproductController::class, 'getStatusproducts'])->name('statusproduct.statusproduct');
    // });
    // // Status Product
    // Route::group(['middleware' => ['permission:ManageMasterproducts']], function () {
    //     Route::get('/Masterproducts', [MasterproductController::class, 'index'])
    //         ->name('pages.Masterproducts');
    //     Route::get('Masterproducts/create', [MasterproductController::class, 'create'])->name('Masterproducts.create');
    //     Route::post('/Masterproducts', [MasterproductController::class, 'store'])->name('Masterproducts.store');
    //     Route::get('/Masterproducts/edit/{hashedId}', [MasterproductController::class, 'edit'])->name('Masterproducts.edit');
    //     Route::put('/Masterproducts/{hashedId}', [MasterproductController::class, 'update'])->name('Masterproducts.update');
    //     Route::get('/masterproducts/masterproducts', [MasterproductController::class, 'getMasterproducts'])->name('masterproducts.masterproducts');
    // });
// Dashboard
// Route::get('/dashboard-ecommerce-dashboard', function () {
//     return view('pages.dashboard-ecommerce-dashboard', ['type_menu' => 'dashboard']);
// });
// // Layout
// Route::get('/layout-default-layout', function () {
//     return view('pages.layout-default-layout', ['type_menu' => 'layout']);
// });
// // Blank Page
// Route::get('/blank-page', function () {
//     return view('pages.blank-page', ['type_menu' => '']);
// });
// // Bootstrap
// Route::get('/bootstrap-alert', function () {
//     return view('pages.bootstrap-alert', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-badge', function () {
//     return view('pages.bootstrap-badge', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-breadcrumb', function () {
//     return view('pages.bootstrap-breadcrumb', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-buttons', function () {
//     return view('pages.bootstrap-buttons', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-card', function () {
//     return view('pages.bootstrap-card', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-carousel', function () {
//     return view('pages.bootstrap-carousel', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-collapse', function () {
//     return view('pages.bootstrap-collapse', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-dropdown', function () {
//     return view('pages.bootstrap-dropdown', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-form', function () {
//     return view('pages.bootstrap-form', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-list-group', function () {
//     return view('pages.bootstrap-list-group', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-media-object', function () {
//     return view('pages.bootstrap-media-object', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-modal', function () {
//     return view('pages.bootstrap-modal', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-nav', function () {
//     return view('pages.bootstrap-nav', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-navbar', function () {
//     return view('pages.bootstrap-navbar', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-pagination', function () {
//     return view('pages.bootstrap-pagination', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-popover', function () {
//     return view('pages.bootstrap-popover', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-progress', function () {
//     return view('pages.bootstrap-progress', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-table', function () {
//     return view('pages.bootstrap-table', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-tooltip', function () {
//     return view('pages.bootstrap-tooltip', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-typography', function () {
//     return view('pages.bootstrap-typography', ['type_menu' => 'bootstrap']);
// });


// // components
// Route::get('/components-article', function () {
//     return view('pages.components-article', ['type_menu' => 'components']);
// });
// Route::get('/components-avatar', function () {
//     return view('pages.components-avatar', ['type_menu' => 'components']);
// });
// Route::get('/components-chat-box', function () {
//     return view('pages.components-chat-box', ['type_menu' => 'components']);
// });
// Route::get('/components-empty-state', function () {
//     return view('pages.components-empty-state', ['type_menu' => 'components']);
// });
// Route::get('/components-gallery', function () {
//     return view('pages.components-gallery', ['type_menu' => 'components']);
// });
// Route::get('/components-hero', function () {
//     return view('pages.components-hero', ['type_menu' => 'components']);
// });
// Route::get('/components-multiple-upload', function () {
//     return view('pages.components-multiple-upload', ['type_menu' => 'components']);
// });
// Route::get('/components-pricing', function () {
//     return view('pages.components-pricing', ['type_menu' => 'components']);
// });
// Route::get('/components-statistic', function () {
//     return view('pages.components-statistic', ['type_menu' => 'components']);
// });
// Route::get('/components-tab', function () {
//     return view('pages.components-tab', ['type_menu' => 'components']);
// });
// Route::get('/components-table', function () {
//     return view('pages.components-table', ['type_menu' => 'components']);
// });
// Route::get('/components-user', function () {
//     return view('pages.components-user', ['type_menu' => 'components']);
// });
// Route::get('/components-wizard', function () {
//     return view('pages.components-wizard', ['type_menu' => 'components']);
// });

// // forms
// Route::get('/forms-advanced-form', function () {
//     return view('pages.forms-advanced-form', ['type_menu' => 'forms']);
// });
// Route::get('/forms-editor', function () {
//     return view('pages.forms-editor', ['type_menu' => 'forms']);
// });
// Route::get('/forms-validation', function () {
//     return view('pages.forms-validation', ['type_menu' => 'forms']);
// });

// // google maps
// // belum tersedia

// // modules
// Route::get('/modules-calendar', function () {
//     return view('pages.modules-calendar', ['type_menu' => 'modules']);
// });
// Route::get('/modules-chartjs', function () {
//     return view('pages.modules-chartjs', ['type_menu' => 'modules']);
// });
// Route::get('/modules-datatables', function () {
//     return view('pages.modules-datatables', ['type_menu' => 'modules']);
// });
// Route::get('/modules-flag', function () {
//     return view('pages.modules-flag', ['type_menu' => 'modules']);
// });
// Route::get('/modules-font-awesome', function () {
//     return view('pages.modules-font-awesome', ['type_menu' => 'modules']);
// });
// Route::get('/modules-ion-icons', function () {
//     return view('pages.modules-ion-icons', ['type_menu' => 'modules']);
// });
// Route::get('/modules-owl-carousel', function () {
//     return view('pages.modules-owl-carousel', ['type_menu' => 'modules']);
// });
// Route::get('/modules-sparkline', function () {
//     return view('pages.modules-sparkline', ['type_menu' => 'modules']);
// });
// Route::get('/modules-sweet-alert', function () {
//     return view('pages.modules-sweet-alert', ['type_menu' => 'modules']);
// });
// Route::get('/modules-toastr', function () {
//     return view('pages.modules-toastr', ['type_menu' => 'modules']);
// });
// Route::get('/modules-vector-map', function () {
//     return view('pages.modules-vector-map', ['type_menu' => 'modules']);
// });
// Route::get('/modules-weather-icon', function () {
//     return view('pages.modules-weather-icon', ['type_menu' => 'modules']);
// });

// // auth
// Route::get('/auth-forgot-password', function () {
//     return view('pages.auth-forgot-password', ['type_menu' => 'auth']);
// });
// Route::get('/auth-login', function () {
//     return view('pages.auth-login', ['type_menu' => 'auth']);
// });

// Route::get('/auth-register', function () {
//     return view('pages.auth-register', ['type_menu' => 'auth']);
// });
// Route::get('/auth-reset-password', function () {
//     return view('pages.auth-reset-password', ['type_menu' => 'auth']);
// });

// // error
// Route::get('/error-403', function () {
//     return view('errors.error-403', ['type_menu' => 'error']);
// });
// Route::get('/error-419', function () {
//     return view('errors.error-419', ['type_menu' => 'error']);
// });
// Route::get('/error-404', function () {
//     return view('errors.error-404', ['type_menu' => 'error']);
// });
// Route::get('/error-500', function () {
//     return view('errors.error-500', ['type_menu' => 'error']);
// });
// Route::get('/error-503', function () {
//     return view('errors.error-503', ['type_menu' => 'error']);
// });
// Route::get('/error-429', function () {
//     return view('errors.error-429', ['type_menu' => 'error']);
// });

// // features
// Route::get('/features-activities', function () {
//     return view('pages.features-activities', ['type_menu' => 'features']);
// });
// Route::get('/features-post-create', function () {
//     return view('pages.features-post-create', ['type_menu' => 'features']);
// });
// Route::get('/features-post', function () {
//     return view('pages.features-post', ['type_menu' => 'features']);
// });
// Route::get('/features-profile', function () {
//     return view('pages.features-profile', ['type_menu' => 'features']);
// });
// Route::get('/features-settings', function () {
//     return view('pages.features-settings', ['type_menu' => 'features']);
// });
// Route::get('/features-setting-detail', function () {
//     return view('pages.features-setting-detail', ['type_menu' => 'features']);
// });
// Route::get('/features-tickets', function () {
//     return view('pages.features-tickets', ['type_menu' => 'features']);
// });

// // utilities
// Route::get('/utilities-contact', function () {
//     return view('pages.utilities-contact', ['type_menu' => 'utilities']);
// });
// Route::get('/utilities-invoice', function () {
//     return view('pages.utilities-invoice', ['type_menu' => 'utilities']);
// });
// Route::get('/utilities-subscribe', function () {
//     return view('pages.utilities-subscribe', ['type_menu' => 'utilities']);
// });

// // credits
// Route::get('/credits', function () {
//     return view('pages.credits', ['type_menu' => '']);
// });






// permissions


// Route::get('/permissions/edit/{hashedId}', [PermissionController::class, 'edit'])->name('permissions.edit');
// Route::get('/permission/permission', [PermissionController::class, 'getPermissions'])->name('permission.permission');

//     // activity log
//     Route::get('/Activity', [ActivityController::class, 'index'])->name('pages.Activity')->middleware('permission:viewActivity');
//     Route::get('/Activity/show/{hashedId}', [ActivityController::class, 'show'])->name('Activity.show')->middleware('permission:showActivity');
//     Route::get('/activity/activity', [ActivityController::class, 'getActivity'])->name('activity.activity')->middleware('permission:viewtableActivity');
//     Route::get('/activity1/activity1', [ActivityController::class, 'getActivity1'])->name('activity1.activity1')->middleware('permission:viewtableActivity1');


// // activity log
// Route::get('/Activity', [ActivityController::class, 'index'])->name('pages.Activity')->middleware('permission:viewActivity');
// Route::get('/Activity/show/{hashedId}', [ActivityController::class, 'show'])->name('Activity.show')->middleware('permission:showActivity');
// Route::get('/activity/activity', [ActivityController::class, 'getActivity'])->name('activity.activity')->middleware('permission:viewtableActivity');
// Route::get('/activity1/activity1', [ActivityController::class, 'getActivity1'])->name('activity1.activity1')->middleware('permission:viewtableActivity1');

// Permissions Routes

// Route::get('/role/role', [RoleController::class, 'getRoles'])->name('role.role');

// User Role Assignment (optional)
// Route::get('/users/{user}/edit-roles', [UserController::class, 'editRoles'])->name('users.editRoles');
// Route::put('/users/{user}/update-roles', [UserController::class, 'updateRoles'])->name('users.updateRoles');





// Route::middleware(['role:Admin', 'auth'])->group(function () {
//     //   dashboardadmin
//     Route::get('/dashboardAdmin', [dashboardAdminController::class, 'index'])->name('pages.dashboardAdmin');
//     Route::get('dashboardAdmin/create', [dashboardAdminController::class, 'create'])->name('dashboardAdmin.create');
//     Route::post('/dashboardAdmin', [dashboardAdminController::class, 'store'])->name('dashboardAdmin.store');
//     Route::get('/dashboardAdmin/edit/{hashedId}', [dashboardAdminController::class, 'edit'])->name('dashboardAdmin.edit');
//     Route::put('/dashboardAdmin/{hashedId}', [dashboardAdminController::class, 'update'])->name('dashboardAdmin.update');
//     Route::get('/users/users', [dashboardAdminController::class, 'getUsers'])->name('users.users');
//     // activity log
//     Route::get('/Activity', [ActivityController::class, 'index'])->name('pages.Activity');
//     Route::get('/Activity/show/{hashedId}', [ActivityController::class, 'show'])->name('Activity.show');
//     Route::get('/activity/activity', [ActivityController::class, 'getActivity'])->name('activity.activity');
//     Route::get('/activity1/activity1', [ActivityController::class, 'getActivity1'])->name('activity1.activity1');


// });