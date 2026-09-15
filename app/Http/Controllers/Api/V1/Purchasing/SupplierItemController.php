<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\StoreSupplierItemRequest;
use App\Http\Requests\Purchasing\UpdateSupplierItemRequest;
use App\Http\Resources\Purchasing\SupplierItemResource;
use App\Services\Purchasing\SupplierItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierItemController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SupplierItemService $supplierItemService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $filters = $request->only(['supplier_id', 'item_id', 'is_active', 'search']);

        $supplierItems = $this->supplierItemService->list($filters, $perPage);

        return SupplierItemResource::collection($supplierItems)
            ->additional([
                'message' => 'Supplier items retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierItemRequest $request): JsonResponse
    {
        $supplierItem = $this->supplierItemService->create($request->validated());

        return (new SupplierItemResource($supplierItem))
            ->additional([
                'message' => 'Supplier item created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $supplierItem = $this->supplierItemService->findById($id);

        return (new SupplierItemResource($supplierItem))
            ->additional([
                'message' => 'Supplier item retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierItemRequest $request, int $id): JsonResponse
    {
        $supplierItem = $this->supplierItemService->findById($id);
        $updated = $this->supplierItemService->update($supplierItem, $request->validated());

        return (new SupplierItemResource($updated))
            ->additional([
                'message' => 'Supplier item updated successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $supplierItem = $this->supplierItemService->findById($id);
        $this->supplierItemService->delete($supplierItem);

        return response()->json([
            'message' => 'Supplier item deleted successfully',
        ], 200);
    }
}
