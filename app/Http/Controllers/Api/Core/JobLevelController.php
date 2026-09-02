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

    public function index(Request $request)
    {
        $data = $this->jobLevelRepository->all($request->search);
        
        return response()->json([
            'message' => 'Job Level list fetched successfully',
            'data' => $data,
        ], 200);
    }

    public function search(Request $request)
    {
        $data = $this->jobLevelRepository->searchJobLevel($request->search);

        return response()->json([
            'message' => 'Job Level search fetched successfully',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'code' => 'required|string|max:10|unique:job_levels,code',
            'name' => 'required|string|max:50|unique:job_levels,name',
            'is_active' => 'boolean',
        ]);

        $validate['is_active'] = $request->has('is_active') ? $request->is_active : true;

        $data = $this->jobLevelRepository->create($validate);

        return response()->json([
            'message' => 'Job Level created successfully',
            'data' => $data,
        ], 201);
    }

    public function show(string $id)
    {
        $data = $this->jobLevelRepository->find($id);

        return response()->json([
            'message' => 'Job Level details fetched successfully',
            'data' => $data
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'code' => 'required|string|max:10|unique:job_levels,code,' . $id,
            'name' => 'required|string|max:50|unique:job_levels,name,' . $id,
            'is_active' => 'boolean',
        ]);

        $data = $this->jobLevelRepository->update($validate, $id);

        return response()->json([
            'message' => 'Job Level updated successfully',
            'data' => $data
        ], 200);
    }

    public function destroy(string $id)
    {
        try {
            $this->jobLevelRepository->delete($id);

            return response()->json([
                'message' => 'Job Level deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'message' => 'Cannot delete Job Level because it is in use.'
                ], 409);
            }
            throw $e;
        }
    }
}
