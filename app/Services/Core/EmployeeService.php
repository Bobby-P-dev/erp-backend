<?php

namespace App\Services\Core;

use App\Repositories\Core\EmployeeRepository;

class EmployeeService
{
    protected EmployeeRepository $employeeRepository;

    public function __construct()
    {
        $this->employeeRepository = new EmployeeRepository();
    }

    public function getAll($search = null, array $filter = [])
    {
        $search = strtolower($search);
        return $this->employeeRepository->all($search, $filter);
    }

    public function create(array $data)
    {
        $data['name'] = strtoupper($data['name']);
        try {
            $employee = $this->employeeRepository->create($data);

        } catch (\Exception $e) {
            throw $e;
        }

        return $employee;
    }

    public function update(array $data, string $id)
    {
        return $this->employeeRepository->update($data, $id);
    }

    public function delete(string $id)
    {
        return $this->employeeRepository->delete($id);
    }
}