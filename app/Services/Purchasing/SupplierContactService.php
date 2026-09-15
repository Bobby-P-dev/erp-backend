<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\SupplierContact;
use App\Repositories\Purchasing\SupplierContactRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SupplierContactService
{
    public function __construct(
        protected SupplierContactRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    public function findById(int $id): SupplierContact
    {
        $contact = $this->repository->find($id);
        if (! $contact) {
            abort(404, 'Supplier contact not found');
        }

        return $contact;
    }

    public function create(array $data): SupplierContact
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['is_primary'])) {
                $this->repository->resetPrimary((int) $data['supplier_id']);
            }

            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): SupplierContact
    {
        $contact = $this->findById($id);

        return DB::transaction(function () use ($contact, $id, $data) {
            $supplierId = $data['supplier_id'] ?? $contact->supplier_id;
            if (! empty($data['is_primary'])) {
                $this->repository->resetPrimary((int) $supplierId, $id);
            }

            return $this->repository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        $this->findById($id);

        return (bool) $this->repository->delete($id);
    }
}
