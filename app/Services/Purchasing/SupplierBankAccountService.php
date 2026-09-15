<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\SupplierBankAccount;
use App\Repositories\Purchasing\SupplierBankAccountRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SupplierBankAccountService
{
    public function __construct(
        protected SupplierBankAccountRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    public function findById(int $id): SupplierBankAccount
    {
        $account = $this->repository->find($id);
        if (! $account) {
            abort(404, 'Supplier bank account not found');
        }

        return $account;
    }

    public function create(array $data): SupplierBankAccount
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['is_primary'])) {
                $this->repository->resetPrimary((int) $data['supplier_id']);
            }

            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): SupplierBankAccount
    {
        $account = $this->findById($id);

        return DB::transaction(function () use ($account, $id, $data) {
            $supplierId = $data['supplier_id'] ?? $account->supplier_id;
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
