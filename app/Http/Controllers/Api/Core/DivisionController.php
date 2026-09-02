<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Repositories\Core\DivisionRepository;
use App\Services\Core\DivisionService;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    protected DivisionService $divisionService;

    protected DivisionRepository $divisionRepository;

    public function __construct()
    {
        $this->divisionService = new DivisionService();
        $this->divisionRepository = new DivisionRepository();
    }

    public function index(Request $request)
    {
        $data = $this->divisionService->getAll($request->search, $request->filter ?? []);

        return response()->json([
            'message' => 'Division list fetched successfully',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required',
            'code' => 'required',
            'is_active' => 'nullable|boolean'
        ]);

        $division = $this->divisionService->store($data);

        return response()->json([
            'message' => 'Division created successfully',
            'data' => $division
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $division = $this->divisionRepository->find($id);

        if (!$division) {
            return response()->json(['message' => 'Division not found'], 404);
        }

        return response()->json([
            'message' => 'Division details fetched successfully',
            'data' => $division
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'name' => 'sometimes|required',
            'code' => 'sometimes|required',
            'is_active' => 'nullable|boolean',
        ]);

        $this->divisionRepository->update($data, $id);

        return response()->json([
            'message' => 'Division updated successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->divisionRepository->delete($id);

        return response()->json([
            'message' => 'Division deleted successfully'
        ], 200);
    }

    public function searchDivision(Request $request)
    {
        $data = $this->divisionRepository->searchDivision($request->search);

        return response()->json([
            'message' => 'Division list fetched successfully',
            'data' => $data
        ], 200);
    }
}
