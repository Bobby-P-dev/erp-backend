<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\StoreSupplierContactRequest;
use App\Http\Requests\Purchasing\UpdateSupplierContactRequest;
use App\Http\Resources\Purchasing\SupplierContactResource;
use App\Services\Purchasing\SupplierContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierContactController extends Controller
{
    public function __construct(
        protected SupplierContactService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $filters = $request->only(['supplier_id', 'is_primary', 'search']);

        $contacts = $this->service->list($filters, $perPage);

        return SupplierContactResource::collection($contacts)
            ->additional([
                'message' => 'Supplier contacts retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreSupplierContactRequest $request): JsonResponse
    {
        $contact = $this->service->create($request->validated());
        $contact->load('supplier');

        return (new SupplierContactResource($contact))
            ->additional([
                'message' => 'Supplier contact created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $contact = $this->service->findById($id);

        return (new SupplierContactResource($contact))
            ->additional([
                'message' => 'Supplier contact retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function update(UpdateSupplierContactRequest $request, int $id): JsonResponse
    {
        $contact = $this->service->update($id, $request->validated());
        $contact->load('supplier');

        return (new SupplierContactResource($contact))
            ->additional([
                'message' => 'Supplier contact updated successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Supplier contact deleted successfully',
        ], 200);
    }
}
