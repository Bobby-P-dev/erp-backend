<?php

namespace App\Repositories\Purchasing;

use App\Models\Purchasing\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository
{
    /**
     * Get paginated suppliers with filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function all(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Supplier::with(['company:id,name,code'])->latest('id');

        if (! empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        if (! empty($filters['supplier_type'])) {
            $query->where('supplier_type', $filters['supplier_type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('supplier_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Find supplier by ID with relations.
     */
    public function find(int $id): ?Supplier
    {
        return Supplier::with([
            'company:id,name,code',
            'contacts',
            'bankAccounts',
            'documents',
        ])->find($id);
    }

    /**
     * Create a new supplier.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Update supplier by ID.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Supplier
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            $supplier->update($data);
        }

        return $supplier;
    }

    /**
     * Soft delete supplier by ID.
     */
    public function delete(int $id): ?bool
    {
        $supplier = Supplier::find($id);

        return $supplier?->delete();
    }

    /**
     * Search active suppliers returning id and name.
     *
     * @return Collection<int, Supplier>
     */
    public function search(?string $search = null, int $limit = 5): Collection
    {
        $query = Supplier::query()
            ->select(['id', 'name'])
            ->where('is_active', true);

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('supplier_code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name', 'asc')->limit($limit)->get();
    }
}
