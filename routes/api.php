<?php

use App\Http\Controllers\Api\Core\AccountingAccountController;
use App\Http\Controllers\Api\Core\AccountingCategoryController;
use App\Http\Controllers\Api\Core\AccountingSubcategoryController;
use App\Http\Controllers\Api\Core\CompanyController;
use App\Http\Controllers\Api\Core\DivisionController;
use App\Http\Controllers\Api\Core\EmployeeController;
use App\Http\Controllers\Api\Core\JobLevelController;
use App\Http\Controllers\Api\Core\PermissionCategoryController;
use App\Http\Controllers\Api\Core\PermissionController;
use App\Http\Controllers\Api\Core\PositionController;
use App\Http\Controllers\Api\Core\RoleController;
use App\Http\Controllers\Api\Core\UserController;
use App\Http\Controllers\Api\V1\Approval\ApprovalDocumentTypeController;
use App\Http\Controllers\Api\V1\Purchasing\PurchaseRequisitionController;
use App\Http\Controllers\Api\V1\Purchasing\SupplierBankAccountController;
use App\Http\Controllers\Api\V1\Purchasing\SupplierContactController;
use App\Http\Controllers\Api\V1\Purchasing\SupplierController;
use App\Http\Controllers\Api\V1\Purchasing\SupplierDocumentController;
use App\Http\Controllers\Api\V1\Purchasing\SupplierItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // companies
    Route::post('companies/store', [CompanyController::class, 'store']);
    Route::get('companies/get-all', [CompanyController::class, 'index']);
    Route::get('companies/search', [CompanyController::class, 'searchCompany']);
    Route::patch('companies/{id}/update', [CompanyController::class, 'update']);
    Route::delete('companies/{id}/delete', [CompanyController::class, 'destroy']);

    // permission category
    Route::get('permission-category/get-all', [PermissionCategoryController::class, 'index']);
    Route::get('permission-category/search', [PermissionCategoryController::class, 'search']);
    Route::post('permission-category/store', [PermissionCategoryController::class, 'store']);
    Route::get('permission-category/{id}/show', [PermissionCategoryController::class, 'show']);
    Route::patch('permission-category/{id}/update', [PermissionCategoryController::class, 'update']);
    Route::delete('permission-category/{id}/delete', [PermissionCategoryController::class, 'destroy']);

    // permission
    Route::get('permission/get-all', [PermissionController::class, 'index']);
    Route::get('permission/search', [PermissionController::class, 'search']);
    Route::post('permission/store', [PermissionController::class, 'store']);
    Route::get('permission/{id}/show', [PermissionController::class, 'show']);
    Route::patch('permission/{id}/update', [PermissionController::class, 'update']);
    Route::delete('permission/{id}/delete', [PermissionController::class, 'destroy']);

    // user
    Route::get('user/get-all', [UserController::class, 'index']);
    Route::post('user/{id}/sync-roles', [UserController::class, 'syncRoles']);
    Route::patch('user/{id}/password', [UserController::class, 'updatePassword']);
    Route::get('user/me', [UserController::class, 'getMe']);

    // role
    Route::get('role/get-all', [RoleController::class, 'index']);
    Route::get('role/search', [RoleController::class, 'search']);
    Route::post('role/store', [RoleController::class, 'store']);
    Route::get('role/{id}/show', [RoleController::class, 'show']);
    Route::patch('role/{id}/update', [RoleController::class, 'update']);
    Route::delete('role/{id}/delete', [RoleController::class, 'destroy']);
    Route::post('role/{id}/sync-permissions', [RoleController::class, 'syncPermissions']);

    // division
    Route::get('division/get-all', [DivisionController::class, 'index']);
    Route::get('division/search', [DivisionController::class, 'searchDivision']);
    Route::post('division/store', [DivisionController::class, 'store']);
    Route::get('division/{id}/show', [DivisionController::class, 'show']);
    Route::patch('division/{id}/update', [DivisionController::class, 'update']);
    Route::delete('division/{id}/delete', [DivisionController::class, 'destroy']);

    // position
    Route::get('position/get-all', [PositionController::class, 'index']);
    Route::get('position/search', [PositionController::class, 'searchPosition']);
    Route::post('position/store', [PositionController::class, 'store']);
    Route::get('position/{id}/show', [PositionController::class, 'show']);
    Route::patch('position/{id}/update', [PositionController::class, 'update']);
    Route::delete('position/{id}/delete', [PositionController::class, 'destroy']);

    // employee
    Route::get('employee/get-all', [EmployeeController::class, 'index']);
    Route::get('employee/search', [EmployeeController::class, 'search']);
    Route::post('employee/store', [EmployeeController::class, 'store']);
    Route::get('employee/{id}/show', [EmployeeController::class, 'show']);
    Route::get('employee/profile', [EmployeeController::class, 'getMe']);
    Route::patch('employee/{id}/update', [EmployeeController::class, 'update']);
    Route::delete('employee/{id}/delete', [EmployeeController::class, 'destroy']);
    Route::get('employee/division', [EmployeeController::class, 'getDivision']);
    Route::get('employee/position/{divisionId}', [EmployeeController::class, 'getPosition']);

    // job level
    Route::get('job-level/get-all', [JobLevelController::class, 'index']);
    Route::get('job-level/search', [JobLevelController::class, 'search']);
    Route::post('job-level/store', [JobLevelController::class, 'store']);
    Route::get('job-level/{id}/show', [JobLevelController::class, 'show']);
    Route::patch('job-level/{id}/update', [JobLevelController::class, 'update']);
    Route::delete('job-level/{id}/delete', [JobLevelController::class, 'destroy']);

    // accounting category
    Route::get('accounting-category/get-all', [AccountingCategoryController::class, 'index']);
    Route::get('accounting-category/search', [AccountingCategoryController::class, 'search']);
    Route::post('accounting-category/store', [AccountingCategoryController::class, 'store']);
    Route::get('accounting-category/{id}/show', [AccountingCategoryController::class, 'show']);
    Route::patch('accounting-category/{id}/update', [AccountingCategoryController::class, 'update']);
    Route::delete('accounting-category/{id}/delete', [AccountingCategoryController::class, 'destroy']);

    // accounting subcategory
    Route::get('accounting-subcategory/get-all', [AccountingSubcategoryController::class, 'index']);
    Route::get('accounting-subcategory/search', [AccountingSubcategoryController::class, 'search']);
    Route::post('accounting-subcategory/store', [AccountingSubcategoryController::class, 'store']);
    Route::get('accounting-subcategory/{id}/show', [AccountingSubcategoryController::class, 'show']);
    Route::patch('accounting-subcategory/{id}/update', [AccountingSubcategoryController::class, 'update']);
    Route::delete('accounting-subcategory/{id}/delete', [AccountingSubcategoryController::class, 'destroy']);

    // accounting account
    Route::get('accounting-account/get-all', [AccountingAccountController::class, 'index']);
    Route::get('accounting-account/search', [AccountingAccountController::class, 'search']);
    Route::post('accounting-account/store', [AccountingAccountController::class, 'store']);
    Route::get('accounting-account/{id}/show', [AccountingAccountController::class, 'show']);
    Route::patch('accounting-account/{id}/update', [AccountingAccountController::class, 'update']);
    Route::delete('accounting-account/{id}/delete', [AccountingAccountController::class, 'destroy']);

    // purchase requisitions
    Route::get('purchase-requisitions', [PurchaseRequisitionController::class, 'index']);
    Route::post('purchase-requisitions', [PurchaseRequisitionController::class, 'store']);
    Route::get('purchase-requisitions/{id}', [PurchaseRequisitionController::class, 'show']);
    Route::post('purchase-requisitions/{id}/submit', [PurchaseRequisitionController::class, 'submit']);

    // suppliers
    Route::get('suppliers', [SupplierController::class, 'index']);
    Route::post('suppliers', [SupplierController::class, 'store']);
    Route::get('suppliers/{id}', [SupplierController::class, 'show']);
    Route::patch('suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('suppliers/{id}', [SupplierController::class, 'destroy']);
    Route::get('supplier/search', [SupplierController::class, 'search']);

    // supplier items
    Route::get('supplier-items', [SupplierItemController::class, 'index']);
    Route::post('supplier-items', [SupplierItemController::class, 'store']);
    Route::get('supplier-items/{id}', [SupplierItemController::class, 'show']);
    Route::patch('supplier-items/{id}', [SupplierItemController::class, 'update']);
    Route::delete('supplier-items/{id}', [SupplierItemController::class, 'destroy']);

    // supplier contacts
    Route::get('supplier-contacts', [SupplierContactController::class, 'index']);
    Route::post('supplier-contacts', [SupplierContactController::class, 'store']);
    Route::get('supplier-contacts/{id}', [SupplierContactController::class, 'show']);
    Route::patch('supplier-contacts/{id}', [SupplierContactController::class, 'update']);
    Route::delete('supplier-contacts/{id}', [SupplierContactController::class, 'destroy']);

    // supplier documents
    Route::get('supplier-documents', [SupplierDocumentController::class, 'index']);
    Route::post('supplier-documents', [SupplierDocumentController::class, 'store']);
    Route::get('supplier-documents/{id}', [SupplierDocumentController::class, 'show']);
    Route::patch('supplier-documents/{id}', [SupplierDocumentController::class, 'update']);
    Route::delete('supplier-documents/{id}', [SupplierDocumentController::class, 'destroy']);
    Route::post('supplier-documents/{id}/verify', [SupplierDocumentController::class, 'verify']);

    // supplier bank accounts
    Route::get('supplier-bank-accounts', [SupplierBankAccountController::class, 'index']);
    Route::post('supplier-bank-accounts', [SupplierBankAccountController::class, 'store']);
    Route::get('supplier-bank-accounts/{id}', [SupplierBankAccountController::class, 'show']);
    Route::patch('supplier-bank-accounts/{id}', [SupplierBankAccountController::class, 'update']);
    Route::delete('supplier-bank-accounts/{id}', [SupplierBankAccountController::class, 'destroy']);

    // approval engine
    Route::get('approval-configurations/document-types', [ApprovalDocumentTypeController::class, 'index']);
    Route::get('approval/document-types', [ApprovalDocumentTypeController::class, 'index']);
});

require __DIR__.'/auth.php';
