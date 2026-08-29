<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Services\Core\PositionService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    protected PositionService $positionService;

    public function __construct()
    {
        $this->positionService = new PositionService();
    }
    public function index(Request $request)
    {
        $data = $this->positionService->getAll($request->search, $request->filter ?? []);

        return response()->json([
            'message' => 'Position list fetched successfully',
            'data' => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:positions,code',
            'division_ids' => 'required|array',
            'division_ids.*' => 'exists:divisions,id',
        ]);

        $position = $this->positionService->store($request->all());

        return response()->json([
            'message' => 'Position created successfully',
            'data' => $position
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
}
