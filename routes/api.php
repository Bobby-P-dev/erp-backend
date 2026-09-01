<?php

use App\Http\Controllers\Api\Core\CompanyController;
use App\Http\Controllers\Api\Core\DivisionController;
use App\Http\Controllers\Api\Core\EmployeeController;
use App\Http\Controllers\Api\Core\JobLevelController;
use App\Http\Controllers\Api\Core\PositionController;
use App\Http\Controllers\Api\Core\RoleController;
use App\Http\Controllers\Api\Core\PermissionController;
use App\Http\Controllers\Api\Core\UserController;
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
    Route::apiResource('roles', RoleController::class);

    Route::post('permission-categories/permissions', [PermissionController::class, 'store']);
    Route::apiResource('permissions', PermissionController::class)->except('store');

    //user
    Route::post('user/{id}/sync-roles', [UserController::class, 'syncRoles']);
    Route::get('user/me', [UserController::class, 'getMe']);


    //role
    Route::post('role', [RoleController::class, 'store']);
    Route::post('role/{id}/sync-permissions', [RoleController::class, 'syncPermissions']);
    Route::get('role/{id}/show', [RoleController::class, 'show']);

    //division
    Route::get('division/search', [DivisionController::class, 'searchDivision']);
    Route::post('division/store', [DivisionController::class, 'store']);

    //position
    Route::get('position/search', [PositionController::class, 'searchPosition']);
    Route::post('position/store', [PositionController::class, 'store']);

    //employee
    Route::get('employee/{id}/show', [EmployeeController::class, 'show']);
    Route::get('employee/profile', [EmployeeController::class, 'getMe'])->middleware('permission:users.create');
    Route::patch('employee/{id}/update', [EmployeeController::class, 'update']);
    Route::delete('employee/{id}/delete', [EmployeeController::class, 'destroy']);

    Route::get('employee/division', [EmployeeController::class, 'getDivision']);
    Route::get('employee/position/{divisionId}', [EmployeeController::class, 'getPosition']);


    //job level
    Route::post('job-level', [JobLevelController::class, 'store']);
    Route::get('job-level', [JobLevelController::class, 'index']);
});

require __DIR__ . '/auth.php';
