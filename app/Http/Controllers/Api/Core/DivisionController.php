<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\DivisionResource;
use App\Services\Core\DivisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function __construct(
        protected DivisionService $divisionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->divisionService->getAll($request->search, $request->filter ?? []);

        return DivisionResource::collection($data)->additional([
            'message' => 'Division list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required',
            'code' => 'required',
            'is_active' => 'nullable|boolean',
        ]);

        $division = $this->divisionService->store($data);

        return (new DivisionResource($division))->additional([
            'message' => 'Division created successfully',
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $division = $this->divisionService->find($id);

        if (! $division) {
            return response()->json(['message' => 'Division not found'], 404);
        }

        return (new DivisionResource($division))->additional([
            'message' => 'Division details fetched successfully',
        ])->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'name' => 'sometimes|required',
            'code' => 'sometimes|required',
            'is_active' => 'nullable|boolean',
        ]);

        $this->divisionService->update($data, $id);

        return response()->json([
            'message' => 'Division updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->divisionService->delete($id);

        return response()->json([
            'message' => 'Division deleted successfully',
        ], 200);
    }

    public function searchDivision(Request $request): JsonResponse
    {
        $data = $this->divisionService->searchDivision($request->search);

        return DivisionResource::collection($data)->additional([
            'message' => 'Division list fetched successfully',
        ])->response()->setStatusCode(200);
    }
}
