<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\StoreSupplierRequest;
use App\Http\Requests\Purchasing\UpdateSupplierRequest;
use App\Http\Resources\Purchasing\SupplierResource;
use App\Services\Purchasing\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(
        protected SupplierService $service
    ) {}

    /**
     * Display a listing of the suppliers.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $filters = $request->only([
            'company_id',
            'is_active',
            'approval_status',
            'supplier_type',
            'search',
        ]);

        $suppliers = $this->service->list($filters, $perPage);

        return SupplierResource::collection($suppliers)
            ->additional([
                'message' => 'Suppliers retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->service->create($request->validated());
        $supplier->load(['company:id,name,code']);

        return (new SupplierResource($supplier))
            ->additional([
                'message' => 'Supplier created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified supplier.
     */
    public function show(int $id): JsonResponse
    {
        $supplier = $this->service->findById($id);

        return (new SupplierResource($supplier))
            ->additional([
                'message' => 'Supplier retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(UpdateSupplierRequest $request, int $id): JsonResponse
    {
        $supplier = $this->service->update($id, $request->validated());
        $supplier->load(['company:id,name,code']);

        return (new SupplierResource($supplier))
            ->additional([
                'message' => 'Supplier updated successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Supplier deleted successfully',
        ], 200);
    }

    /**
     * Search active suppliers returning id and name.
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $limit = (int) $request->query('limit', 5);

        $data = $this->service->search($search, $limit);

        return SupplierResource::collection($data)
            ->additional([
                'message' => 'Supplier search results fetched successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }
}
