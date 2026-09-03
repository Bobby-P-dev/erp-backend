<?php

namespace App\Repositories\Core;

use App\Models\Core\Division;
use App\Models\Core\DivisionPosition;
use App\Models\Core\Employee;
use App\Models\Core\Position;
use Auth;
use Illuminate\Cache\RateLimiting\Limit;

class EmployeeRepository
{
    public function all($search = null, array $filter = [])
    {
        $query = Employee::with(['company', 'division', 'position', 'jobLevel'])->orderBy('nik', 'asc');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filter['company_id']) && filled($filter['company_id'])) {
            $query->where('company_id', $filter['company_id']);
        }

        if (isset($filter['division_id']) && filled($filter['division_id'])) {
            $query->where('division_id', $filter['division_id']);
        }

        if (isset($filter['position_id']) && filled($filter['position_id'])) {
            $query->where('position_id', $filter['position_id']);
        }

        if (isset($filter['job_level_id']) && filled($filter['job_level_id'])) {
            $query->where('job_level_id', $filter['job_level_id']);
        }

        if (isset($filter['is_active']) && filled($filter['is_active'])) {
            $query->where('is_active', $filter['is_active']);
        }

        return $query->paginate(10);
    }

    public function show(int $id)
    {

        $data = Employee::with(['company', 'division', 'position', 'jobLevel'])->findOrFail($id);

        return $data;
    }

    public function getMe()
    {
        $employeeId = Auth::user()->employee_id;

        $data = Employee::with(['company', 'division', 'position', 'jobLevel'])->find($employeeId);

        return $data;
    }

    public function create(array $data)
    {
        return Employee::create($data);
    }

    public function update(array $data, string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->updateOrFail($data);
        return $employee;
    }

    public function delete(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->deleteOrFail();
        return $employee;
    }

    public function getDivision($search, $companyId = null)
    {
        $query = Division::select("id", "name")->where("is_active", true);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if (filled($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->limit(10)->get();
    }

    public function getPosition($search, $divisionId)
    {
        $query = DivisionPosition::with('position')->where('division_id', $divisionId);

        $query->searchPosition($search);

        return $query->limit(10)->get();
    }

    public function searchEmployee($search)
    {
        $query = Employee::select('id', 'nik', 'name')
            ->where('is_active', true);

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->limit(5)->get();
    }
}