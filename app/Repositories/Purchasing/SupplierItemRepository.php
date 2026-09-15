<?php

namespace App\Repositories\Purchasing;

use App\Models\Purchasing\SupplierItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierItemRepository
{
    /**
     * Default relations to eager load.
     *
     * @var array<int, string>
     */
    protected array $defaultRelations = [
        'supplier',
        'item',
    ];

    /**
     * Get paginated list of supplier items with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function all(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SupplierItem::with($this->defaultRelations);

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (! empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('supplier_item_code', 'like', "%{$search}%")
                    ->orWhere('supplier_item_name', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($itemQuery) use ($search) {
                        $itemQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('supplier_code', 'like', "%{$search}%");
                    });
            });
        }

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Find a supplier item by ID with relations.
     */
    public function findOrFail(int $id): SupplierItem
    {
        return SupplierItem::with($this->defaultRelations)->findOrFail($id);
    }

    /**
     * Find a supplier item by ID with relations without failing.
     */
    public function find(int $id): ?SupplierItem
    {
        return SupplierItem::with($this->defaultRelations)->find($id);
    }

    /**
     * Create a new supplier item record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SupplierItem
    {
        $supplierItem = SupplierItem::create($data);

        return $this->loadRelations($supplierItem);
    }

    /**
     * Update an existing supplier item.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SupplierItem $supplierItem, array $data): SupplierItem
    {
        $supplierItem->update($data);

        return $this->loadRelations($supplierItem);
    }

    /**
     * Delete a supplier item record.
     */
    public function delete(SupplierItem $supplierItem): bool
    {
        return (bool) $supplierItem->delete();
    }

    /**
     * Eager load default relations.
     */
    public function loadRelations(SupplierItem $supplierItem): SupplierItem
    {
        return $supplierItem->load($this->defaultRelations);
    }
}
