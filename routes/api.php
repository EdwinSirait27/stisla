<?php

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

/*
|--------------------------------------------------------------------------
| Integrasi antar aplikasi (server ke server)
|--------------------------------------------------------------------------
| Dipanggil aplikasi formrequest, wajib header X-Api-Key (lihat VerifyApiKey).
| Path tidak diubah supaya formrequest cukup menambah header.
| Dihapus: /user (melewati device check mobile), /finance, /employee, /signature
| (method tidak ada di ApiController dan tidak dipanggil aplikasi mana pun).
*/
Route::middleware(['api.key', 'throttle:60,1'])->group(function () {
    Route::get('/company/{id}', [CompanyController::class, 'show']);
    Route::get('/manager/{employeeId}', [ApiController::class, 'getManagerByEmployee']);
});

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
Route::middleware(['auth:sanctum', 'device.check', 'mobile.access'])->group(function () {
    Route::post('/mobile/logout', [AuthController::class, 'logout']);
    Route::get('/mobile/me', [AuthController::class, 'me']);
    Route::get('/mobile/profile', [AuthController::class, 'profile']);
    // Attendance — throttle ketat karena hit DeepFace service
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/mobile/attendance/checkin', [AttendanceController::class, 'checkin']);
        Route::post('/mobile/attendance/checkout', [AttendanceController::class, 'checkout']);
    });
    // Read-only endpoints
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/mobile/attendance/history', [AttendanceController::class, 'history']);
        Route::get('/mobile/attendance/today', [AttendanceController::class, 'today']);
        Route::get('/mobile/roster', [AttendanceController::class, 'myRoster']);
                Route::post('/mobile/push-token', [AuthController::class, 'registerPushToken']); // tambahkan di sini

    });
});