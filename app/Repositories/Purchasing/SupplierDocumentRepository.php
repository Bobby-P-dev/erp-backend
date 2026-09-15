<?php

namespace App\Repositories\Purchasing;

use App\Models\Purchasing\SupplierDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierDocumentRepository
{
    public function all(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SupplierDocument::with(['supplier:id,supplier_code,name', 'file', 'verifiedBy'])->latest('id');

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (! empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $query->where('is_verified', filter_var($filters['is_verified'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                    ->orWhere('document_type', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?SupplierDocument
    {
        return SupplierDocument::with(['supplier:id,supplier_code,name', 'file', 'verifiedBy'])->find($id);
    }

    public function create(array $data): SupplierDocument
    {
        return SupplierDocument::create($data);
    }

    public function update(int $id, array $data): ?SupplierDocument
    {
        $document = SupplierDocument::find($id);
        if ($document) {
            $document->update($data);
        }

        return $document;
    }

    public function delete(int $id): ?bool
    {
        $document = SupplierDocument::find($id);

        return $document?->delete();
    }

    public function verify(int $id, int $verifiedByUserId): ?SupplierDocument
    {
        $document = SupplierDocument::find($id);
        if ($document) {
            $document->update([
                'is_verified' => true,
                'verified_by' => $verifiedByUserId,
                'verified_at' => now(),
            ]);
            $document->load(['supplier', 'file', 'verifiedBy']);
        }

        return $document;
    }
}
