<?php

namespace App\Services\Core;

use App\Models\Core\Company;
use App\Repositories\Core\CompanyRepository;

class CompanyService
{
    public function __construct(
        protected CompanyRepository $companyRepository
    ) {}

    public function store(array $data): Company
    {
        $data['code'] = strtoupper($data['code']);
        $data['name'] = strtoupper($data['name']);

        return $this->companyRepository->create($data);
    }

    public function getAll(?string $search = null)
    {
        if (! empty($search)) {
            $search = strtoupper((string) $search);
        }

        return $this->companyRepository->all($search);
    }

    public function update(array $data, int|string $id): ?Company
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }
        if (isset($data['name'])) {
            $data['name'] = strtoupper($data['name']);
        }

        return $this->companyRepository->update($data, $id);
    }

    public function delete(int|string $id): ?Company
    {
        return $this->companyRepository->delete($id);
    }

    public function searchCompany(?string $search = null)
    {
        return $this->companyRepository->searchCompany($search);
    }
}
