<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\StoreSupplierBankAccountRequest;
use App\Http\Requests\Purchasing\UpdateSupplierBankAccountRequest;
use App\Http\Resources\Purchasing\SupplierBankAccountResource;
use App\Services\Purchasing\SupplierBankAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierBankAccountController extends Controller
{
    public function __construct(
        protected SupplierBankAccountService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $filters = $request->only(['supplier_id', 'is_primary', 'is_active', 'search']);

        $accounts = $this->service->list($filters, $perPage);

        return SupplierBankAccountResource::collection($accounts)
            ->additional([
                'message' => 'Supplier bank accounts retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreSupplierBankAccountRequest $request): JsonResponse
    {
        $account = $this->service->create($request->validated());
        $account->load('supplier');

        return (new SupplierBankAccountResource($account))
            ->additional([
                'message' => 'Supplier bank account created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $account = $this->service->findById($id);

        return (new SupplierBankAccountResource($account))
            ->additional([
                'message' => 'Supplier bank account retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function update(UpdateSupplierBankAccountRequest $request, int $id): JsonResponse
    {
        $account = $this->service->update($id, $request->validated());
        $account->load('supplier');

        return (new SupplierBankAccountResource($account))
            ->additional([
                'message' => 'Supplier bank account updated successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Supplier bank account deleted successfully',
        ], 200);
    }
}
