<?php

namespace App\Services\Core;

use App\Models\Core\Division;
use App\Repositories\Core\DivisionRepository;

class DivisionService
{
    public function __construct(
        protected DivisionRepository $divisionRepository
    ) {}

    public function store(array $data): Division
    {
        $data['code'] = strtoupper($data['code']);
        $data['name'] = strtoupper($data['name']);

        return $this->divisionRepository->create($data);
    }

    public function getAll(?string $search = null, array $filter = [])
    {
        if (! empty($search)) {
            $search = strtoupper($search);
        }

        return $this->divisionRepository->all($search, $filter);
    }

    public function find(int|string $id): ?Division
    {
        return $this->divisionRepository->find($id);
    }

    public function update(array $data, int|string $id)
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }
        if (isset($data['name'])) {
            $data['name'] = strtoupper($data['name']);
        }

        return $this->divisionRepository->update($data, $id);
    }

    public function delete(int|string $id)
    {
        return $this->divisionRepository->delete($id);
    }

    public function searchDivision(?string $search = null)
    {
        return $this->divisionRepository->searchDivision($search);
    }
}
