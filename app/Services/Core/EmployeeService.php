<?php

namespace App\Services\Core;

use App\Models\Core\DivisionPosition;
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

        if (empty($data['position_id']) && empty($data['job_level_id'])) {
            throw new \Exception('Position and Job Level cannot both be null. At least one must be provided.');
        }

        $divisionId = $data['division_id'] ?? null;
        $positionId = $data['position_id'] ?? null;
        $jobLevelId = $data['job_level_id'] ?? null;

        if ($positionId) {
            $divPos = DivisionPosition::where('division_id', $divisionId)
                ->where('position_id', $positionId)
                ->first();

            if (!$divPos) {
                throw new \Exception('Division and Position not found');
            }
        }

        try {
            $employee = $this->employeeRepository->create([
                'name' => $data['name'],
                'nik' => $data['nik'],
                'company_id' => $data['company_id'],
                'division_id' => $divisionId,
                'position_id' => $positionId,
                'job_level_id' => $jobLevelId,
            ]);

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