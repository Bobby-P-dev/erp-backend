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

        return $this->companyRepository->create($data);
    }

    public function getAll($search = null)
    {
        $search = strtoupper($search);

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