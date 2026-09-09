<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\PurchaseRequisition;
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

            $purchaseRequisition = PurchaseRequisition::create($headerData);

            if (! empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $purchaseRequisition->items()->create([
                        'item_id' => $itemData['item_id'],
                        'unit_id' => $itemData['unit_id'],
                        'quantity' => $itemData['quantity'],
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }

            $purchaseRequisition->load([
                'company',
                'division',
                'requester.employee',
                'items.item',
                'items.unit',
            ]);

            return $purchaseRequisition;
        });
    }

    /**
     * Get a paginated list of Purchase Requisitions.
     */
    public function list(int $perPage = 10): LengthAwarePaginator
    {
        return PurchaseRequisition::with([
            'company',
            'division',
            'requester.employee',
            'items.item',
            'items.unit',
        ])->latest('id')->paginate($perPage);
    }

    /**
     * Find a Purchase Requisition by its ID with all relations.
     */
    public function findById(int $id): PurchaseRequisition
    {
        return PurchaseRequisition::with([
            'company',
            'division',
            'requester.employee',
            'items.item',
            'items.unit',
        ])->findOrFail($id);
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

        $purchaseRequisition->status = 'submitted';
        $purchaseRequisition->save();

        $purchaseRequisition->load([
            'company',
            'division',
            'requester.employee',
            'items.item',
            'items.unit',
        ]);

        return $purchaseRequisition;
    }
}
