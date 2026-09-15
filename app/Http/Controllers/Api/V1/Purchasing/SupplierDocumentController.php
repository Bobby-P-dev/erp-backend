<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\StoreSupplierDocumentRequest;
use App\Http\Requests\Purchasing\UpdateSupplierDocumentRequest;
use App\Http\Resources\Purchasing\SupplierDocumentResource;
use App\Services\Purchasing\SupplierDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierDocumentController extends Controller
{
    public function __construct(
        protected SupplierDocumentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $filters = $request->only(['supplier_id', 'document_type', 'is_active', 'is_verified', 'search']);

        $documents = $this->service->list($filters, $perPage);

        return SupplierDocumentResource::collection($documents)
            ->additional([
                'message' => 'Supplier documents retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreSupplierDocumentRequest $request): JsonResponse
    {
        $document = $this->service->create($request->validated());

        return (new SupplierDocumentResource($document))
            ->additional([
                'message' => 'Supplier document created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $document = $this->service->findById($id);

        return (new SupplierDocumentResource($document))
            ->additional([
                'message' => 'Supplier document retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function update(UpdateSupplierDocumentRequest $request, int $id): JsonResponse
    {
        $document = $this->service->update($id, $request->validated());

        return (new SupplierDocumentResource($document))
            ->additional([
                'message' => 'Supplier document updated successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Supplier document deleted successfully',
        ], 200);
    }

    public function verify(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $document = $this->service->verify($id, $userId);

        return (new SupplierDocumentResource($document))
            ->additional([
                'message' => 'Supplier document verified successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }
}
