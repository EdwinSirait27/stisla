<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\StructuresnewController;

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
Route::get('/employee/{employeeId}/manager', [StructuresnewController::class, 'getManagerByEmployee']);
Route::get('/employee/{employeeId}/manager', [StructuresnewController::class, 'getManagerByEmployee']);
// Route::get('/employee/{employeeId}', [StructuresnewController::class, 'getEmployeeByPosition']);
// Route::get('/employee/position/{positionName}', [StructuresnewController::class, 'getEmployeeByPosition']);
Route::get('/employee/{employeeId}', [StructuresnewController::class, 'getEmployeeById']);