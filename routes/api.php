<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
// use App\Http\Controllers\StructuresnewController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/company/{id}', [CompanyController::class, 'show']);
Route::get('/finance/{id}', [ApiController::class, 'show']);
Route::get('/manager/{employeeId}', [ApiController::class, 'getManagerByEmployee']);
Route::get('/employee/{employeeId}', [ApiController::class, 'getEmployeeById']);
Route::get('/signature/{id}', [ApiController::class, 'showsignature'])
    ->name('signature.show');

// Route::post('/mobile/login', [AuthController::class, 'login']);
// Route::get('/mobile/config', [AuthController::class, 'appConfig']);
// Route::get('/mobile/photo/{path}', [AuthController::class, 'photo'])
//     ->where('path', '.*')
//     ->name('mobile.photo');
// // Protected - wajib Bearer token valid
// Route::middleware(['auth:sanctum','device.check'])->group(function () {
//     Route::post('/mobile/logout', [AuthController::class, 'logout']);
//     Route::get('/mobile/me', [AuthController::class, 'me']);
//     Route::get('/mobile/profile', [AuthController::class, 'profile']);
//     Route::post('/mobile/attendance/checkin', [AttendanceController::class, 'checkin']);
//     Route::post('/mobile/attendance/checkout', [AttendanceController::class, 'checkout']);
//     Route::get('/mobile/attendance/history', [AttendanceController::class, 'history']);
//     Route::get('/mobile/attendance/today', [AttendanceController::class, 'today']);
//     Route::get('/mobile/roster', [AttendanceController::class, 'myRoster']);
// });
// Public routes
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/mobile/login', [AuthController::class, 'login']);
});

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/mobile/config', [AuthController::class, 'appConfig']);
    Route::get('/mobile/photo/{path}', [AuthController::class, 'photo'])
        ->where('path', '.*')
        ->name('mobile.photo');
});

// Protected routes
Route::middleware(['auth:sanctum', 'device.check'])->group(function () {
    Route::post('/mobile/logout', [AuthController::class, 'logout']);
    Route::get('/mobile/me', [AuthController::class, 'me']);
    Route::get('/mobile/profile', [AuthController::class, 'profile']);

    // Attendance — throttle ketat karena hit DeepFace service
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/mobile/attendance/checkin', [AttendanceController::class, 'checkin']);
        Route::post('/mobile/attendance/checkout', [AttendanceController::class, 'checkout']);
    });

    // Read-only endpoints
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/mobile/attendance/history', [AttendanceController::class, 'history']);
        Route::get('/mobile/attendance/today', [AttendanceController::class, 'today']);
        Route::get('/mobile/roster', [AttendanceController::class, 'myRoster']);
    });
});