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
        if (isset($data['name'])) {
            $data['name'] = strtoupper($data['name']);
        }

        $employee = $this->employeeRepository->show($id);

        $divisionId = $data['division_id'] ?? $employee->division_id;
        $positionId = array_key_exists('position_id', $data) ? $data['position_id'] : $employee->position_id;
        $jobLevelId = array_key_exists('job_level_id', $data) ? $data['job_level_id'] : $employee->job_level_id;

        if (empty($positionId) && empty($jobLevelId)) {
            throw new \Exception('Position and Job Level cannot both be null. At least one must be provided.');
        }

        if ($positionId) {
            $divPos = DivisionPosition::where('division_id', $divisionId)
                ->where('position_id', $positionId)
                ->first();

            if (!$divPos) {
                throw new \Exception('Division and Position not found');
            }
        }

        try {
            $updateData = [];
            if (isset($data['name'])) $updateData['name'] = $data['name'];
            if (isset($data['nik'])) $updateData['nik'] = $data['nik'];
            if (isset($data['company_id'])) $updateData['company_id'] = $data['company_id'];
            if (isset($data['division_id'])) $updateData['division_id'] = $data['division_id'];
            if (array_key_exists('position_id', $data)) $updateData['position_id'] = $data['position_id'];
            if (array_key_exists('job_level_id', $data)) $updateData['job_level_id'] = $data['job_level_id'];
            if (array_key_exists('is_active', $data)) $updateData['is_active'] = $data['is_active'];
            if (array_key_exists('email', $data)) $updateData['email'] = $data['email'];

            $employee = $this->employeeRepository->update($updateData, $id);

        } catch (\Exception $e) {
            throw $e;
        }

        return $employee;
    }

}