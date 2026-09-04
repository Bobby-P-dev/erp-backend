<?php

namespace App\Repositories\Core;

use App\Models\Core\JobLevel;

class JobLevelRepository
{
    public function all($search = null)
    {
        $query = JobLevel::select('id', 'name', 'code', 'is_active')->latest();
        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function searchJobLevel($search = null)
    {
        $query = JobLevel::select('id', 'name', 'code')->where('is_active', true);

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->limit(5)->get();
    }

    public function find($id)
    {
        return JobLevel::findOrFail($id);
    }

    public function create(array $data)
    {
        return JobLevel::create($data);
    }

    public function update(array $data, string $id)
    {
        $jobLevel = JobLevel::findOrFail($id);
        $jobLevel->updateOrFail($data);
        return $jobLevel;
    }

    public function delete(string $id)
    {
        $jobLevel = JobLevel::findOrFail($id);
        $jobLevel->deleteOrFail();
        return $jobLevel;
    }
}