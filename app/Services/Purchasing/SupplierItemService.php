<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\SupplierItem;
use App\Repositories\Purchasing\SupplierItemRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierItemService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected SupplierItemRepository $supplierItemRepository
    ) {}

    /**
     * Get paginated list of supplier items with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->supplierItemRepository->all($filters, $perPage);
    }

    /**
     * Store a newly created supplier item.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SupplierItem
    {
        return $this->supplierItemRepository->create($data);
    }

    /**
     * Find a supplier item by its ID.
     */
    public function findById(int $id): SupplierItem
    {
        return $this->supplierItemRepository->findOrFail($id);
    }

    /**
     * Update an existing supplier item.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SupplierItem $supplierItem, array $data): SupplierItem
    {
        return $this->supplierItemRepository->update($supplierItem, $data);
    }

    /**
     * Delete a supplier item.
     */
    public function delete(SupplierItem $supplierItem): bool
    {
        return $this->supplierItemRepository->delete($supplierItem);
    }
}
