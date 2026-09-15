<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\PurchaseRequisition;
use App\Repositories\Purchasing\PurchaseRequisitionRepository;
use App\Services\Core\DocumentNumberService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PurchaseRequisitionService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected PurchaseRequisitionRepository $purchaseRequisitionRepository,
        protected DocumentNumberService $documentNumberService
    ) {}

    /**
     * Create a new Purchase Requisition with its items inside a database transaction.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function create(array $data): PurchaseRequisition
    {
        return DB::transaction(function () use ($data) {
            $period = isset($data['request_date'])
                ? (string) date('Y', strtotime($data['request_date']))
                : null;

            $prNumber = $this->documentNumberService->generatePurchaseRequisitionNumber(
                (int) $data['company_id'],
                $period
            );

            $headerData = [
                'company_id' => $data['company_id'],
                'division_id' => $data['division_id'],
                'requester_id' => $data['requester_id'],
                'pr_number' => $prNumber,
                'request_date' => $data['request_date'],
                'required_date' => $data['required_date'] ?? null,
                'status' => 'draft',
                'purpose' => $data['purpose'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            $purchaseRequisition = $this->purchaseRequisitionRepository->create($headerData);

            if (! empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $this->purchaseRequisitionRepository->createItem($purchaseRequisition, [
                        'item_id' => $itemData['item_id'],
                        'unit_id' => $itemData['unit_id'],
                        'quantity' => $itemData['quantity'],
                        'accounting_category_id' => $itemData['accounting_category_id'] ?? null,
                        'accounting_subcategory_id' => $itemData['accounting_subcategory_id'] ?? null,
                        'accounting_account_id' => $itemData['accounting_account_id'] ?? null,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }

            return $this->purchaseRequisitionRepository->loadRelations($purchaseRequisition);
        });
    }

    /**
     * Get a paginated list of Purchase Requisitions.
     */
    public function list(int $perPage = 10): LengthAwarePaginator
    {
        return $this->purchaseRequisitionRepository->paginate($perPage);
    }

    /**
     * Find a Purchase Requisition by its ID with all relations.
     */
    public function findById(int $id): PurchaseRequisition
    {
        return $this->purchaseRequisitionRepository->findOrFail($id);
    }

    /**
     * Submit a Purchase Requisition.
     *
     * @throws RuntimeException
     */
    public function submit(PurchaseRequisition $purchaseRequisition): PurchaseRequisition
    {
        if ($purchaseRequisition->status !== 'draft') {
            throw new RuntimeException("Purchase Requisition with status '{$purchaseRequisition->status}' cannot be submitted.");
        }

        $this->purchaseRequisitionRepository->update($purchaseRequisition, [
            'status' => 'submitted',
        ]);

        return $this->purchaseRequisitionRepository->loadRelations($purchaseRequisition);
    }
}
