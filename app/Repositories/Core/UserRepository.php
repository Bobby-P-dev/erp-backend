<?php

namespace App\Repositories\Core;

use App\Models\User;

class UserRepository
{
    public function find($id)
    {
        return User::findOrFail($id);
    }

    public function getAll($search = null, array $filters = [])
    {
        $query = User::with([
            'roles',
            'employee.company',
            'employee.division',
            'employee.position',
            'employee.jobLevel',
            'supplier'
        ])->latest();

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($empQ) use ($search) {
                    $empQ->where('name', 'like', "%{$search}%");
                })->orWhereHas('supplier', function ($supQ) use ($search) {
                    $supQ->where('name', 'like', "%{$search}%")
                        ->orWhere('supplier_code', 'like', "%{$search}%");
                });
            });
        }

        if (isset($filters['company_id']) && filled($filters['company_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('company_id', $filters['company_id']);
            });
        }

        if (isset($filters['division_id']) && filled($filters['division_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('division_id', $filters['division_id']);
            });
        }

        if (isset($filters['position_id']) && filled($filters['position_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('position_id', $filters['position_id']);
            });
        }

        if (isset($filters['job_level_id']) && filled($filters['job_level_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('job_level_id', $filters['job_level_id']);
            });
        }

        if (isset($filters['role_id']) && filled($filters['role_id'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('id', $filters['role_id']);
            });
        }

        return $query->paginate(10);
    }

    public function syncRoles(User $user, array $roles)
    {
        return $user->syncRoles($roles);
    }
}
