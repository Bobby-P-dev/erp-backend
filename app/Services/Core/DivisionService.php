<?php

namespace App\Services\Core;

use App\Models\Core\Division;
use App\Repositories\Core\DivisionRepository;

class DivisionService
{
    protected DivisionRepository $divisionRepository;

    public function __construct()
    {
        $this->divisionRepository = new DivisionRepository();
    }

    public function store(array $data): Division
    {
        $data['code'] = strtoupper($data['code']);
        $data['name'] = strtoupper($data['name']);

        try {
            return $this->divisionRepository->create($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAll($search, $filter = [])
    {
        $search = strtoupper($search);

        return $this->divisionRepository->all($search, $filter);
    }

    public function update(array $data, $id)
    {
        $data['code'] = strtoupper($data['code']);
        $data['name'] = strtoupper($data['name']);

        return $this->divisionRepository->update($data, $id);
    }

    public function delete($id)
    {
        return $this->divisionRepository->delete($id);
    }
}