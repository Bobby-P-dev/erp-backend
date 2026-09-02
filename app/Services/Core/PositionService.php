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
        $search = strtoupper((string) $search);
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
        try {
            DB::beginTransaction();

            if (isset($data['name'])) {
                $data['name'] = strtoupper($data['name']);
            }
            if (isset($data['code'])) {
                $data['code'] = strtoupper($data['code']);
            }

            $positionData = collect($data)->except('division_ids')->toArray();
            
            $this->positionRepository->update($positionData, $id);
            $position = $this->positionRepository->find($id);

            if (isset($data['division_ids'])) {
                $this->positionRepository->updateDivisions($position, $data['division_ids']);
            }

            DB::commit();
            return $position;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        return Position::where('id', $id)->delete();
    }
}