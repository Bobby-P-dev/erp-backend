<?php

namespace App\Services\Core;

use App\Models\Core\Company;
use App\Repositories\Core\CompanyRepository;

class CompanyService
{
    protected CompanyRepository $companyRepository;

    public function __construct()
    {
        $this->companyRepository = new CompanyRepository();
    }

    public function store(array $data): Company
    {
        $data['code'] = strtoupper($data['code']);
        $data['name'] = strtoupper($data['name']);

        try {
            return $this->companyRepository->create($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAll($search)
    {
        if (!empty($search)) {
            $search = strtoupper((string) $search);
        }

        return $this->companyRepository->all($search);
    }


    public function update(array $data, $id)
    {
        $data['code'] = strtoupper($data['code']);
        $data['name'] = strtoupper($data['name']);

        return $this->companyRepository->update($data, $id);
    }

    public function delete($id)
    {
        return $this->companyRepository->delete($id);
    }
}