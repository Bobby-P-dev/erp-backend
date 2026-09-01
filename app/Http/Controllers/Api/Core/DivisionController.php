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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
