<?php

use App\Http\Controllers\Api\Core\CompanyController;
use App\Http\Controllers\Api\Core\DivisionController;
use App\Http\Controllers\Api\Core\EmployeeController;
use App\Http\Controllers\Api\Core\JobLevelController;
use App\Http\Controllers\Api\Core\PositionController;
use App\Http\Controllers\Api\Core\RoleController;
use App\Http\Controllers\Api\Core\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('divisions', DivisionController::class);
    Route::apiResource('positions', PositionController::class);
    Route::apiResource('employees', EmployeeController::class);
    Route::get('employee/division', [EmployeeController::class, 'getDivision']);
    Route::get('employee/position/{divisionId}', [EmployeeController::class, 'getPosition']);
    Route::apiResource('roles', RoleController::class)->middleware('permission:users.create');

    Route::post('permission-categories/permissions', [PermissionController::class, 'store']);
    Route::apiResource('permissions', PermissionController::class)->except('store');


    //job level
    Route::post('job-level', [JobLevelController::class, 'store']);
    Route::get('job-level', [JobLevelController::class, 'index']);
});

require __DIR__ . '/auth.php';
