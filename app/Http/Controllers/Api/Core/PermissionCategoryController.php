<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Repositories\Core\PermissionCategoryRepository;
use Illuminate\Http\Request;

class PermissionCategoryController extends Controller
{
    protected PermissionCategoryRepository $permissionCategoryRepository;

    public function __construct()
    {
        $this->permissionCategoryRepository = new PermissionCategoryRepository();
    }

    public function index(Request $request)
    {
        $data = $this->permissionCategoryRepository->all($request->search);

        return response()->json([
            'message' => 'Permission category list fetched successfully',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string",
        ]);

        $permissionCategory = $this->permissionCategoryRepository->create($data);

        return response()->json([
            'message' => 'Permission category created successfully',
            'data' => $permissionCategory,
        ], 201);
    }

    public function show(string $id)
    {
        $permissionCategory = $this->permissionCategoryRepository->find($id);

        if (!$permissionCategory) {
            return response()->json(['message' => 'Permission category not found'], 404);
        }

        return response()->json([
            'message' => 'Permission category details fetched successfully',
            'data' => $permissionCategory
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            "name" => "sometimes|required|string",
        ]);

        $this->permissionCategoryRepository->update($data, $id);

        return response()->json([
            'message' => 'Permission category updated successfully'
        ], 200);
    }

    public function destroy(string $id)
    {
        $this->permissionCategoryRepository->delete($id);

        return response()->json([
            'message' => 'Permission category deleted successfully'
        ], 200);
    }

    public function search(Request $request)
    {
        $data = $this->permissionCategoryRepository->searchPermissionCategory($request->search);

        return response()->json([
            'message' => 'Permission category list fetched successfully',
            'data' => $data
        ], 200);
    }
}
