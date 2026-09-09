<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\StorePurchaseRequisitionRequest;
use App\Http\Resources\Purchasing\PurchaseRequisitionResource;
use App\Services\Purchasing\PurchaseRequisitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PurchaseRequisitionController extends Controller
{
    /**
     * Create a new controller instance with dependency injection.
     */
    public function __construct(
        protected PurchaseRequisitionService $purchaseRequisitionService
    ) {}

    /**
     * Display a paginated listing of Purchase Requisitions.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $purchaseRequisitions = $this->purchaseRequisitionService->list($perPage);

        return PurchaseRequisitionResource::collection($purchaseRequisitions)
            ->additional([
                'message' => 'Purchase Requisitions retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Store a newly created Purchase Requisition in storage.
     */
    public function store(StorePurchaseRequisitionRequest $request): JsonResponse
    {
        $purchaseRequisition = $this->purchaseRequisitionService->create($request->validated());

        return (new PurchaseRequisitionResource($purchaseRequisition))
            ->additional([
                'message' => 'Purchase Requisition created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified Purchase Requisition.
     */
    public function show(int $id): JsonResponse
    {
        $purchaseRequisition = $this->purchaseRequisitionService->findById($id);

        return (new PurchaseRequisitionResource($purchaseRequisition))
            ->additional([
                'message' => 'Purchase Requisition retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Submit the specified Purchase Requisition.
     */
    public function submit(int $id): JsonResponse
    {
        try {
            $purchaseRequisition = $this->purchaseRequisitionService->findById($id);
            $submitted = $this->purchaseRequisitionService->submit($purchaseRequisition);

            return (new PurchaseRequisitionResource($submitted))
                ->additional([
                    'message' => 'Purchase Requisition submitted successfully',
                ])
                ->response()
                ->setStatusCode(200);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
