<?php

namespace App\Repositories\Core;

use App\Models\Core\Position;

class PositionRepository
{
    public function all($search = null, array $filter = [])
    {
        $query = Position::with(['divisions:id,company_id,name', 'divisions.company:id,name'])->orderBy('name', 'asc');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($filter['division_ids'])) {
            $query->whereHas('divisions', function ($q) use ($filter) {
                $q->whereIn('division_id', $filter['division_ids']);
            });
        }

        if (isset($filter['is_active']) && filled($filter['is_active'])) {
            $query->where('is_active', $filter['is_active']);
        }

        return $query->paginate(10);
    }

    public function find($id)
    {
        return Position::find($id);
    }

    public function create(array $data)
    {
        return Position::create($data);
    }

    public function update(array $data, $id)
    {
        return Position::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return Position::where('id', $id)->delete();
    }

    public function attachDivisions($position, array $divisionIds)
    {
        return $position->divisions()->sync($divisionIds);
    }

    public function detachDivisions($position, array $divisionIds)
    {
        return $position->divisions()->detach($divisionIds);
    }

    public function updateDivisions($position, array $divisionIds)
    {
        return $position->divisions()->sync($divisionIds);
    }
}