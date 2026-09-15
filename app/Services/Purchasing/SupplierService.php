<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\Supplier;
use App\Repositories\Purchasing\SupplierRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupplierService
{
    public function __construct(
        protected SupplierRepository $repository
    ) {}

    /**
     * Get paginated suppliers.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Find supplier by ID or fail.
     */
    public function findById(int $id): Supplier
    {
        $supplier = $this->repository->find($id);
        if (! $supplier) {
            abort(404, 'Supplier not found');
        }

        return $supplier;
    }

    /**
     * Create a new supplier.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Supplier
    {
        if (empty($data['supplier_code'])) {
            $data['supplier_code'] = $this->generateSupplierCode();
        }

        return $this->repository->create($data);
    }

    /**
     * Update supplier by ID.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): Supplier
    {
        $this->findById($id);

        $updated = $this->repository->update($id, $data);

        return $updated ?? $this->findById($id);
    }

    /**
     * Delete supplier by ID.
     */
    public function delete(int $id): bool
    {
        $this->findById($id);

        return (bool) $this->repository->delete($id);
    }

    /**
     * Search active suppliers.
     *
     * @return Collection<int, Supplier>
     */
    public function search(?string $search = null, int $limit = 5): Collection
    {
        return $this->repository->search($search, $limit);
    }

    /**
     * Generate automatic supplier code if omitted.
     */
    protected function generateSupplierCode(): string
    {
        $maxId = (int) Supplier::withTrashed()->max('id') + 1;

        return sprintf('SUP-%04d', $maxId);
    }
}
