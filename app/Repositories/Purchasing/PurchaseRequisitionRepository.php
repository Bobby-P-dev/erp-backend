<?php

namespace App\Repositories\Purchasing;

use App\Models\Purchasing\PurchaseRequisition;
use App\Models\Purchasing\PurchaseRequisitionItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PurchaseRequisitionRepository
{
    /**
     * Default relations to eager load.
     *
     * @var array<int, string>
     */
    protected array $defaultRelations = [
        'company',
        'division',
        'requester.employee',
        'items.item',
        'items.unit',
    ];

    /**
     * Get paginated list of Purchase Requisitions.
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return PurchaseRequisition::with($this->defaultRelations)
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Find a Purchase Requisition by its ID with relations.
     */
    public function findOrFail(int $id): PurchaseRequisition
    {
        return PurchaseRequisition::with($this->defaultRelations)->findOrFail($id);
    }

    /**
     * Find a Purchase Requisition by its ID without failing.
     */
    public function find(int $id): ?PurchaseRequisition
    {
        return PurchaseRequisition::with($this->defaultRelations)->find($id);
    }

    /**
     * Create a new Purchase Requisition header record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PurchaseRequisition
    {
        return PurchaseRequisition::create($data);
    }

    /**
     * Create an item line for a Purchase Requisition.
     *
     * @param  array<string, mixed>  $itemData
     */
    public function createItem(PurchaseRequisition $purchaseRequisition, array $itemData): PurchaseRequisitionItem
    {
        return $purchaseRequisition->items()->create($itemData);
    }

    /**
     * Update an existing Purchase Requisition.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(PurchaseRequisition $purchaseRequisition, array $data): PurchaseRequisition
    {
        $purchaseRequisition->update($data);

        return $purchaseRequisition;
    }

    /**
     * Eager load default relations onto the instance.
     */
    public function loadRelations(PurchaseRequisition $purchaseRequisition): PurchaseRequisition
    {
        return $purchaseRequisition->load($this->defaultRelations);
    }
}
