<?php

namespace App\Repositories\Core;

use App\Models\Core\JobLevel;

class JobLevelRepository
{
    public function all($search = null)
    {
        $query = JobLevel::orderBy('name', 'asc');
        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function create(array $data)
    {
        $jobLevel = JobLevel::where('code', $data['code'])->first();
        if ($jobLevel) {
            throw new \Exception('Code already exists');
        }

        $jobLevel = JobLevel::where('name', $data['name'])->first();
        if ($jobLevel) {
            throw new \Exception('Name already exists');
        }

        return JobLevel::create($data);
    }

    public function update(array $data, string $id)
    {
        $jobLevel = JobLevel::find($id);
        $jobLevel->update($data);
        return $jobLevel;
    }

    public function delete(string $id)
    {
        $jobLevel = JobLevel::find($id);
        $jobLevel->delete();
        return $jobLevel;
    }
}