<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\PositionResource;
use App\Services\Core\PositionService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function __construct(
        protected PositionService $positionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->positionService->getAll($request->search, $request->filter ?? []);

        return PositionResource::collection($data)->additional([
            'message' => 'Position list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'name' => 'required',
            'code' => 'required|unique:positions,code',
            'division_ids' => 'required|array',
            'division_ids.*' => 'exists:divisions,id',
        ]);

        $position = $this->positionService->store($validate);

        return (new PositionResource($position))->additional([
            'message' => 'Position created successfully',
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $position = $this->positionService->find($id);
        if (! $position) {
            return response()->json(['message' => 'Position not found'], 404);
        }

        return (new PositionResource($position))->additional([
            'message' => 'Position details fetched successfully',
        ])->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validate = $request->validate([
            'name' => 'sometimes|required',
            'code' => 'sometimes|required|unique:positions,code,'.$id,
            'is_active' => 'nullable|boolean',
            'division_ids' => 'sometimes|array',
            'division_ids.*' => 'exists:divisions,id',
        ]);

        $position = $this->positionService->update($validate, $id);

        return (new PositionResource($position))->additional([
            'message' => 'Position updated successfully',
        ])->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->positionService->delete($id);

            return response()->json([
                'message' => 'Position deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                return response()->json([
                    'message' => 'Posisi tidak bisa dihapus karena sedang digunakan oleh karyawan.',
                ], 409);
            }

            throw $e;
        }
    }

    public function searchPosition(Request $request): JsonResponse
    {
        $data = $this->positionService->searchPosition($request->search);

        return PositionResource::collection($data)->additional([
            'message' => 'Position list fetched successfully',
        ])->response()->setStatusCode(200);
    }
}
