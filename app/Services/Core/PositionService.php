<?php

namespace App\Services\Core;

use App\Models\Core\Position;
use App\Repositories\Core\PositionRepository;
use Illuminate\Support\Facades\DB;

class PositionService
{
    protected PositionRepository $positionRepository;

    public function __construct()
    {
        $this->positionRepository = new PositionRepository();
    }

    public function getAll($search = null, $filter = [])
    {
        $search = strtoupper($search);
        return $this->positionRepository->all($search, $filter);
    }

    public function store(array $data)
    {
        $data['name'] = strtoupper($data['name']);
        $data['code'] = strtoupper($data['code']);

        try {
            DB::beginTransaction();
            $position = $this->positionRepository->create($data);


            if (!empty($data['division_ids'])) {
                $this->positionRepository->attachDivisions($position, $data['division_ids']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }


        return $position;
    }

    public function update(array $data, $id)
    {
        $data['name'] = strtoupper($data['name']);
        return Position::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return Position::where('id', $id)->delete();
    }
}