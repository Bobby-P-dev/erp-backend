<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Repositories\Core\JobLevelRepository;
use Illuminate\Http\Request;

class JobLevelController extends Controller
{
    protected JobLevelRepository $jobLevelRepository;

    public function __construct(JobLevelRepository $jobLevelRepository)
    {
        $this->jobLevelRepository = $jobLevelRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->jobLevelRepository->all($request->search);
        return response()->json([
            'status' => true,
            'message' => 'Data retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $data = $this->jobLevelRepository->create($validate);

        return response()->json([
            'message' => 'Data created successfully',
            'data' => $data,
        ]);
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
