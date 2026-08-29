<?php

use App\Http\Controllers\Api\Core\CompanyController;
use App\Http\Controllers\Api\Core\DivisionController;
use App\Http\Controllers\Api\Core\EmployeeController;
use App\Http\Controllers\Api\Core\PositionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('divisions', DivisionController::class);
    Route::apiResource('positions', PositionController::class);
    Route::apiResource('employees', EmployeeController::class);
});

require __DIR__ . '/auth.php';
