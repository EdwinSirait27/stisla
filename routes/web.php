<?php

use App\Http\Controllers\dashboardAdminController;
use App\Http\Controllers\dashboardManagerController;
use App\Http\Controllers\dashboardKasirController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardHRController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ActivityController;
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
Route::match(['GET', 'POST'], '/logout', [LoginController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');
Route::middleware(['auth', 'role:Admin'])->group(function () {
    // Dashboard
    Route::group(['middleware' => ['permission:dashboardAdmin']], function () {
    Route::get('/dashboardAdmin', [DashboardAdminController::class, 'index'])
        ->name('pages.dashboardAdmin');
        // ->middleware('permission:dashboardAdmin');
    // Route::group(['middleware' => ['permission:ManageUser']], function () {
        Route::get('dashboardAdmin/create', [dashboardAdminController::class, 'create'])->name('dashboardAdmin.create');
        Route::post('/dashboardAdmin', [dashboardAdminController::class, 'store'])->name('dashboardAdmin.store');
        Route::get('/dashboardAdmin/edit/{hashedId}', [dashboardAdminController::class, 'edit'])->name('dashboardAdmin.edit');
        Route::get('/dashboardAdmin/show/{hashedId}', [dashboardAdminController::class, 'show'])->name('dashboardAdmin.show');
        Route::put('/dashboardAdmin/{hashedId}', [dashboardAdminController::class, 'update'])->name('dashboardAdmin.update');
        Route::get('/users/users', [dashboardAdminController::class, 'getUsers'])->name('users.users');
    // });
    // Route::group(['middleware' => ['permission:ManageActivity']], function () {
        // activity log
        Route::get('/Activity', [ActivityController::class, 'index'])->name('pages.Activity');
        Route::get('/Activity/show/{hashedId}', [ActivityController::class, 'show'])->name('Activity.show');
        Route::get('/activity/activity', [ActivityController::class, 'getActivity'])->name('activity.activity');
        Route::get('/activity1/activity1', [ActivityController::class, 'getActivity1'])->name('activity1.activity1');
    // });
    //    roles
    // Route::group(['middleware' => ['permission:ManageRoles']], function () {

        Route::get('/roles', [RoleController::class, 'index'])
            ->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/edit/{hashedId}', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{hashedId}', [RoleController::class, 'update'])->name('roles.update');
        Route::get('/role/role', [RoleController::class, 'getRoles'])->name('role.role');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // });
    Route::get('/permissions', [PermissionController::class, 'index'])
        ->name('permissions.index');
    Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/edit/{hashedId}', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{hashedId}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::get('/permissions/permissions', [PermissionController::class, 'getPermissions'])->name('permissions.permissions');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
});

// Route::group(['middleware' => ['permission:ManageActivity']], function () {
    // activity log
    Route::get('/Activity', [ActivityController::class, 'index'])->name('pages.Activity');
    Route::get('/Activity/show/{hashedId}', [ActivityController::class, 'show'])->name('Activity.show');
    Route::get('/activity/activity', [ActivityController::class, 'getActivity'])->name('activity.activity');
    Route::get('/activity1/activity1', [ActivityController::class, 'getActivity1'])->name('activity1.activity1');
// });
//    roles
// Route::group(['middleware' => ['permission:ManageRoles']], function () {

    Route::get('/roles', [RoleController::class, 'index'])
        ->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/edit/{hashedId}', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{hashedId}', [RoleController::class, 'update'])->name('roles.update');
    Route::get('/role/role', [RoleController::class, 'getRoles'])->name('role.role');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/permissions', [PermissionController::class, 'index'])
        ->name('permissions.index');
    Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/edit/{hashedId}', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{hashedId}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::get('/permissions/permissions', [PermissionController::class, 'getPermissions'])->name('permissions.permissions');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
// });
     });
// headHR
Route::middleware(['auth', 'role:HeadHR'])->group(function () {
    Route::group(['middleware' => ['permission:dashboardHR']], function () {

    Route::get('/dashboardHR', [DashboardHRController::class, 'index'])
    ->name('pages.dashboardHR');
});


// employee
    Route::get('/Employee', [EmployeeController::class, 'index'])
    ->name('pages.Employee');
    Route::get('Employee/create', [EmployeeController::class, 'create'])->name('Employee.create');
    Route::post('/Employee', [EmployeeController::class, 'store'])->name('Employee.store');
    Route::get('/Employee/edit/{hashedId}', [EmployeeController::class, 'edit'])->name('Employee.edit');
    Route::get('/Employee/show/{hashedId}', [EmployeeController::class, 'show'])->name('Employee.show');
    Route::put('/Employee/{hashedId}', [EmployeeController::class, 'update'])->name('Employee.update');
    Route::get('/employees/employees', [EmployeeController::class, 'getEmployees'])->name('employees.employees');
    
// Position    
    Route::get('/Position', [PositionController::class, 'index'])
    ->name('pages.Position');
    Route::get('Position/create', [PositionController::class, 'create'])->name('Position.create');
    Route::post('/Position', [PositionController::class, 'store'])->name('Position.store');
    Route::get('/Position/edit/{hashedId}', [PositionController::class, 'edit'])->name('Position.edit');
    Route::put('/Position/{hashedId}', [PositionController::class, 'update'])->name('Position.update');
    Route::get('/positions/positions', [PositionController::class, 'getPositions'])->name('positions.positions');
// Department    
    Route::get('/Department', [DepartmentController::class, 'index'])
    ->name('pages.Department');
    Route::get('Department/create', [DepartmentController::class, 'create'])->name('Department.create');
    Route::post('/Department', [DepartmentController::class, 'store'])->name('Department.store');
    Route::get('/Department/edit/{hashedId}', [DepartmentController::class, 'edit'])->name('Department.edit');
    Route::put('/Department/{hashedId}', [DepartmentController::class, 'update'])->name('Department.update');
    Route::get('/departments/departments', [DepartmentController::class, 'getDepartments'])->name('departments.departments');



   
});







 // Route::group(['middleware' => ['permission:dashboardHR']], function () {
    //     Route::get('/dashboardHR', [DashboardHRController::class, 'index'])
    //         ->name('pages.dashboardHR');
    //     Route::get('/users/users', [DashboardHRController::class, 'getUsers'])->name('users.users');
    // });
    // Route::group(['middleware' => ['permission:CreateEmployee']], function () {
    //     Route::get('dashboardAdmin/create', [DashboardHRController::class, 'create'])->name('dashboardAdmin.create');
    //     Route::post('/dashboardAdmin', [DashboardHRController::class, 'store'])->name('dashboardAdmin.store');
    // });
    // Route::group(['middleware' => ['permission:EditEmployee']], function () {

    //     Route::get('/dashboardAdmin/edit/{hashedId}', [DashboardHRController::class, 'edit'])->name('dashboardAdmin.edit');
    //     Route::put('/dashboardAdmin/{hashedId}', [DashboardHRController::class, 'update'])->name('dashboardAdmin.update');
    // });




Route::middleware(['auth', 'role:ManagerStore'])->group(function () {

    Route::group(['middleware' => ['permission:dashboardManager']], function () {

        Route::get('/dashboardManager', [dashboardManagerController::class, 'index'])->name('pages.dashboardManager');

    });

});
Route::middleware(['can:isKasir', 'auth'])->group(function () {
    Route::get('/dashboardKasir', [dashboardKasirController::class, 'index'])->name('pages.dashboardKasir');
});
Route::group(['middleware' => 'guest'], function () {
    Route::middleware(['throttle:10,1'])->group(function () {
        Route::post('/session', [LoginController::class, 'store'])->name('session');
        Route::get('/', [LoginController::class, 'index'])->name('login');
        Route::get('/portofolio', function () {
            return view('pages.portofolio');
        });
    });
});



















































// Dashboard

Route::get('/dashboard-ecommerce-dashboard', function () {
    return view('pages.dashboard-ecommerce-dashboard', ['type_menu' => 'dashboard']);
});


// Layout
Route::get('/layout-default-layout', function () {
    return view('pages.layout-default-layout', ['type_menu' => 'layout']);
});

// Blank Page
Route::get('/blank-page', function () {
    return view('pages.blank-page', ['type_menu' => '']);
});

// Bootstrap
Route::get('/bootstrap-alert', function () {
    return view('pages.bootstrap-alert', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-badge', function () {
    return view('pages.bootstrap-badge', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-breadcrumb', function () {
    return view('pages.bootstrap-breadcrumb', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-buttons', function () {
    return view('pages.bootstrap-buttons', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-card', function () {
    return view('pages.bootstrap-card', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-carousel', function () {
    return view('pages.bootstrap-carousel', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-collapse', function () {
    return view('pages.bootstrap-collapse', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-dropdown', function () {
    return view('pages.bootstrap-dropdown', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-form', function () {
    return view('pages.bootstrap-form', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-list-group', function () {
    return view('pages.bootstrap-list-group', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-media-object', function () {
    return view('pages.bootstrap-media-object', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-modal', function () {
    return view('pages.bootstrap-modal', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-nav', function () {
    return view('pages.bootstrap-nav', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-navbar', function () {
    return view('pages.bootstrap-navbar', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-pagination', function () {
    return view('pages.bootstrap-pagination', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-popover', function () {
    return view('pages.bootstrap-popover', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-progress', function () {
    return view('pages.bootstrap-progress', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-table', function () {
    return view('pages.bootstrap-table', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-tooltip', function () {
    return view('pages.bootstrap-tooltip', ['type_menu' => 'bootstrap']);
});
Route::get('/bootstrap-typography', function () {
    return view('pages.bootstrap-typography', ['type_menu' => 'bootstrap']);
});


// components
Route::get('/components-article', function () {
    return view('pages.components-article', ['type_menu' => 'components']);
});
Route::get('/components-avatar', function () {
    return view('pages.components-avatar', ['type_menu' => 'components']);
});
Route::get('/components-chat-box', function () {
    return view('pages.components-chat-box', ['type_menu' => 'components']);
});
Route::get('/components-empty-state', function () {
    return view('pages.components-empty-state', ['type_menu' => 'components']);
});
Route::get('/components-gallery', function () {
    return view('pages.components-gallery', ['type_menu' => 'components']);
});
Route::get('/components-hero', function () {
    return view('pages.components-hero', ['type_menu' => 'components']);
});
Route::get('/components-multiple-upload', function () {
    return view('pages.components-multiple-upload', ['type_menu' => 'components']);
});
Route::get('/components-pricing', function () {
    return view('pages.components-pricing', ['type_menu' => 'components']);
});
Route::get('/components-statistic', function () {
    return view('pages.components-statistic', ['type_menu' => 'components']);
});
Route::get('/components-tab', function () {
    return view('pages.components-tab', ['type_menu' => 'components']);
});
Route::get('/components-table', function () {
    return view('pages.components-table', ['type_menu' => 'components']);
});
Route::get('/components-user', function () {
    return view('pages.components-user', ['type_menu' => 'components']);
});
Route::get('/components-wizard', function () {
    return view('pages.components-wizard', ['type_menu' => 'components']);
});

// forms
Route::get('/forms-advanced-form', function () {
    return view('pages.forms-advanced-form', ['type_menu' => 'forms']);
});
Route::get('/forms-editor', function () {
    return view('pages.forms-editor', ['type_menu' => 'forms']);
});
Route::get('/forms-validation', function () {
    return view('pages.forms-validation', ['type_menu' => 'forms']);
});

// google maps
// belum tersedia

// modules
Route::get('/modules-calendar', function () {
    return view('pages.modules-calendar', ['type_menu' => 'modules']);
});
Route::get('/modules-chartjs', function () {
    return view('pages.modules-chartjs', ['type_menu' => 'modules']);
});
Route::get('/modules-datatables', function () {
    return view('pages.modules-datatables', ['type_menu' => 'modules']);
});
Route::get('/modules-flag', function () {
    return view('pages.modules-flag', ['type_menu' => 'modules']);
});
Route::get('/modules-font-awesome', function () {
    return view('pages.modules-font-awesome', ['type_menu' => 'modules']);
});
Route::get('/modules-ion-icons', function () {
    return view('pages.modules-ion-icons', ['type_menu' => 'modules']);
});
Route::get('/modules-owl-carousel', function () {
    return view('pages.modules-owl-carousel', ['type_menu' => 'modules']);
});
Route::get('/modules-sparkline', function () {
    return view('pages.modules-sparkline', ['type_menu' => 'modules']);
});
Route::get('/modules-sweet-alert', function () {
    return view('pages.modules-sweet-alert', ['type_menu' => 'modules']);
});
Route::get('/modules-toastr', function () {
    return view('pages.modules-toastr', ['type_menu' => 'modules']);
});
Route::get('/modules-vector-map', function () {
    return view('pages.modules-vector-map', ['type_menu' => 'modules']);
});
Route::get('/modules-weather-icon', function () {
    return view('pages.modules-weather-icon', ['type_menu' => 'modules']);
});

// auth
Route::get('/auth-forgot-password', function () {
    return view('pages.auth-forgot-password', ['type_menu' => 'auth']);
});
Route::get('/auth-login', function () {
    return view('pages.auth-login', ['type_menu' => 'auth']);
});

Route::get('/auth-register', function () {
    return view('pages.auth-register', ['type_menu' => 'auth']);
});
Route::get('/auth-reset-password', function () {
    return view('pages.auth-reset-password', ['type_menu' => 'auth']);
});

// error
Route::get('/error-403', function () {
    return view('errors.error-403', ['type_menu' => 'error']);
});
Route::get('/error-419', function () {
    return view('errors.error-419', ['type_menu' => 'error']);
});
Route::get('/error-404', function () {
    return view('errors.error-404', ['type_menu' => 'error']);
});
Route::get('/error-500', function () {
    return view('errors.error-500', ['type_menu' => 'error']);
});
Route::get('/error-503', function () {
    return view('errors.error-503', ['type_menu' => 'error']);
});
Route::get('/error-429', function () {
    return view('errors.error-429', ['type_menu' => 'error']);
});

// features
Route::get('/features-activities', function () {
    return view('pages.features-activities', ['type_menu' => 'features']);
});
Route::get('/features-post-create', function () {
    return view('pages.features-post-create', ['type_menu' => 'features']);
});
Route::get('/features-post', function () {
    return view('pages.features-post', ['type_menu' => 'features']);
});
Route::get('/features-profile', function () {
    return view('pages.features-profile', ['type_menu' => 'features']);
});
Route::get('/features-settings', function () {
    return view('pages.features-settings', ['type_menu' => 'features']);
});
Route::get('/features-setting-detail', function () {
    return view('pages.features-setting-detail', ['type_menu' => 'features']);
});
Route::get('/features-tickets', function () {
    return view('pages.features-tickets', ['type_menu' => 'features']);
});

// utilities
Route::get('/utilities-contact', function () {
    return view('pages.utilities-contact', ['type_menu' => 'utilities']);
});
Route::get('/utilities-invoice', function () {
    return view('pages.utilities-invoice', ['type_menu' => 'utilities']);
});
Route::get('/utilities-subscribe', function () {
    return view('pages.utilities-subscribe', ['type_menu' => 'utilities']);
});

// credits
Route::get('/credits', function () {
    return view('pages.credits', ['type_menu' => '']);
});
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