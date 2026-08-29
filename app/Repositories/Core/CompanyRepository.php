<?php

namespace App\Repositories\Core;

use App\Models\Core\Company;

class CompanyRepository
{
    public function all($search = null)
    {
        $query = Company::select('name', 'code')->orderBy('name', 'asc');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function find($id)
    {
        return Company::find($id);
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(array $data, $id)
    {
        $company = $this->find($id);
        $company->update($data);
        return $company;
    }

    public function delete($id)
    {
        $company = $this->find($id);
        $company->delete();
        return $company;
    }
}