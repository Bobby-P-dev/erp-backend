<?php

namespace App\Repositories\Core;

use App\Models\Core\Division;

class DivisionRepository
{
    public function all($search = null, array $filter = [])
    {
        $query = Division::with('company:id,name')->select('id', 'company_id', 'name', 'code')->orderBy('name', 'asc');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filter['company_id'])) {
            $query->where('company_id', $filter['company_id']);
        }

        if (isset($filter['is_active']) && filled($filter['is_active'])) {
            $query->where('is_active', $filter['is_active']);
        }

        return $query->paginate(10);
    }

    public function find($id)
    {
        return Division::find($id);
    }

    public function create(array $data)
    {
        return Division::create($data);
    }

    public function update(array $data, $id)
    {
        $data['code'] = strtoupper($data['code']);
        $data['name'] = strtoupper($data['name']);

        return Division::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return Division::where('id', $id)->delete();
    }
}
