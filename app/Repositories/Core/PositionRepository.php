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
        return Position::firstOrCreate(['name' => $data['name']], $data);
    }

    public function update(array $data, $id)
    {
        return Position::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        $position = $this->find($id);
        if ($position) {
            $position->divisions()->detach();
            $position->delete();
        }
        return $position;
    }

    public function attachDivisions(Position $position, array $divisionIds)
    {
        return $position->divisions()->syncWithoutDetaching($divisionIds);
    }

    public function detachDivisions(Position $position, array $divisionIds)
    {
        return $position->divisions()->detach($divisionIds);
    }

    public function updateDivisions($position, array $divisionIds)
    {
        return $position->divisions()->sync($divisionIds);
    }

    public function searchPosition($search = null)
    {
        $query = Position::select('id', 'code', 'name');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->where('is_active', true)->take(5)->get();
    }
}