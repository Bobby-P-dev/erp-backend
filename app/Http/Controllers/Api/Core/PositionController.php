<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Resources\Core\PositionResource;

use App\Http\Controllers\Controller;
use App\Repositories\Core\PositionRepository;
use App\Services\Core\PositionService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    protected PositionService $positionService;
    protected PositionRepository $positionRepository;

    public function __construct()
    {
        $this->positionService = new PositionService();
        $this->positionRepository = new PositionRepository();
    }
    public function index(Request $request)
    {
        $data = $this->positionService->getAll($request->search, $request->filter ?? []);

        return PositionResource::collection($data)->additional([
            'message' => 'Position list fetched successfully'
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required',
            'code' => 'required|unique:positions,code',
            'division_ids' => 'required|array',
            'division_ids.*' => 'exists:divisions,id',
        ]);

        $position = $this->positionService->store($validate);

        return response()->json([
            'message' => 'Position created successfully',
            'data' => new PositionResource($position)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $position = $this->positionRepository->find($id);
        if (!$position) {
            return response()->json(['message' => 'Position not found'], 404);
        }
        $position->load('divisions:id,company_id,name');

        return response()->json([
            'message' => 'Position details fetched successfully',
            'data' => $position
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'sometimes|required',
            'code' => 'sometimes|required|unique:positions,code,' . $id,
            'is_active' => 'nullable|boolean',
            'division_ids' => 'sometimes|array',
            'division_ids.*' => 'exists:divisions,id',
        ]);

        $position = $this->positionService->update($validate, $id);

        return response()->json([
            'message' => 'Position updated successfully',
            'data' => new PositionResource($position)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->positionRepository->delete($id);

            return response()->json([
                'message' => 'Position deleted successfully'
            ], 200);
        } catch (QueryException $e) {
            if ($e->getCode() == "23000") {
                return response()->json([
                    'message' => 'Posisi tidak bisa dihapus karena sedang digunakan oleh karyawan.'
                ], 409);
            }

            throw $e;
        }
    }

    public function searchPosition(Request $request)
    {
        $data = $this->positionRepository->searchPosition($request->search);

        return response()->json([
            'message' => 'Position list fetched successfully',
            'data' => PositionResource::collection($data)
        ], 200);
    }
}
