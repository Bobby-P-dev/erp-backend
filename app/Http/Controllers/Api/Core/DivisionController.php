<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Services\Core\DivisionService;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    protected DivisionService $divisionService;

    public function __construct()
    {
        $this->divisionService = new DivisionService();
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

        try {
            $division = $this->divisionService->store($data);

            return response()->json([
                'message' => 'Division created successfully',
                'data' => $division
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create division',
                'error' => $e->getMessage()
            ], 500);
        }
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
}
