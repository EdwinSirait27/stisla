<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
// use App\Http\Controllers\StructuresnewController;
use App\Http\Controllers\ApiController;

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

// Route::middleware(['auth'])->group(function () {

Route::get('/company/{id}', [CompanyController::class, 'show']);
Route::get('/finance/{id}', [ApiController::class, 'show']);
// Route::get('/employee/{employeeId}/manager', [ApiController::class, 'getManagerByEmployee']);
Route::get('/manager/{employeeId}', [ApiController::class, 'getManagerByEmployee']);
Route::get('/employee/{employeeId}', [ApiController::class, 'getEmployeeById']);
// });