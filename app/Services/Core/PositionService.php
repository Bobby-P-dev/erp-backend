<?php

namespace App\Services\Core;

use App\Models\Core\Position;
use App\Repositories\Core\PositionRepository;
use Illuminate\Support\Facades\DB;

class PositionService
{
    public function __construct(
        protected PositionRepository $positionRepository
    ) {}

    public function getAll(?string $search = null, array $filter = [])
    {
        if (! empty($search)) {
            $search = strtoupper($search);
        }

        return $this->positionRepository->all($search, $filter);
    }

    public function find(int|string $id): ?Position
    {
        $position = $this->positionRepository->find($id);
        $position?->load('divisions:id,company_id,name');

        return $position;
    }

    public function store(array $data): Position
    {
        $data['name'] = strtoupper($data['name']);
        $data['code'] = strtoupper($data['code']);

        try {
            DB::beginTransaction();
            $position = $this->positionRepository->create($data);

            if (! empty($data['division_ids'])) {
                $this->positionRepository->attachDivisions($position, $data['division_ids']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $position;
    }

    public function update(array $data, int|string $id): ?Position
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

    public function delete(int|string $id)
    {
        return $this->positionRepository->delete($id);
    }

    public function searchPosition(?string $search = null)
    {
        return $this->positionRepository->searchPosition($search);
    }
}
