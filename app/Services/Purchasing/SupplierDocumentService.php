<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\SupplierDocument;
use App\Repositories\Purchasing\SupplierDocumentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierDocumentService
{
    public function __construct(
        protected SupplierDocumentRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    public function findById(int $id): SupplierDocument
    {
        $document = $this->repository->find($id);
        if (! $document) {
            abort(404, 'Supplier document not found');
        }

        return $document;
    }

    public function create(array $data): SupplierDocument
    {
        $document = $this->repository->create($data);
        $document->load(['supplier', 'file', 'verifiedBy']);

        return $document;
    }

    public function update(int $id, array $data): SupplierDocument
    {
        $this->findById($id);
        $updated = $this->repository->update($id, $data);
        $updated?->load(['supplier', 'file', 'verifiedBy']);

        return $updated;
    }

    public function delete(int $id): bool
    {
        $this->findById($id);

        return (bool) $this->repository->delete($id);
    }

    public function verify(int $id, int $userId): SupplierDocument
    {
        $this->findById($id);

        return $this->repository->verify($id, $userId);
    }
}
