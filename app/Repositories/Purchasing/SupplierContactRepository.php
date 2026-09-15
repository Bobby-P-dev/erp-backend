<?php

namespace App\Repositories\Purchasing;

use App\Models\Purchasing\SupplierContact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierContactRepository
{
    public function all(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SupplierContact::with('supplier:id,supplier_code,name')->latest('id');

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['is_primary']) && $filters['is_primary'] !== '') {
            $query->where('is_primary', filter_var($filters['is_primary'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?SupplierContact
    {
        return SupplierContact::with('supplier:id,supplier_code,name')->find($id);
    }

    public function create(array $data): SupplierContact
    {
        return SupplierContact::create($data);
    }

    public function update(int $id, array $data): ?SupplierContact
    {
        $contact = SupplierContact::find($id);
        if ($contact) {
            $contact->update($data);
        }

        return $contact;
    }

    public function delete(int $id): ?bool
    {
        $contact = SupplierContact::find($id);

        return $contact?->delete();
    }

    public function resetPrimary(int $supplierId, ?int $exceptId = null): void
    {
        $query = SupplierContact::where('supplier_id', $supplierId)
            ->where('is_primary', true);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update(['is_primary' => false]);
    }
}
