<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\PermissionCategoryResource;
use App\Repositories\Core\PermissionCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionCategoryController extends Controller
{
    public function __construct(
        protected PermissionCategoryRepository $permissionCategoryRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->permissionCategoryRepository->all($request->search);

        return PermissionCategoryResource::collection($data)->additional([
            'message' => 'Permission category list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
        ]);

        $permissionCategory = $this->permissionCategoryRepository->create($data);

        return (new PermissionCategoryResource($permissionCategory))->additional([
            'message' => 'Permission category created successfully',
        ])->response()->setStatusCode(201);
    }

    public function show(string $id): JsonResponse
    {
        $permissionCategory = $this->permissionCategoryRepository->find($id);

        if (! $permissionCategory) {
            return response()->json(['message' => 'Permission category not found'], 404);
        }

        return (new PermissionCategoryResource($permissionCategory))->additional([
            'message' => 'Permission category details fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
        ]);

        $this->permissionCategoryRepository->update($data, $id);

        return response()->json([
            'message' => 'Permission category updated successfully',
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->permissionCategoryRepository->delete($id);

        return response()->json([
            'message' => 'Permission category deleted successfully',
        ], 200);
    }

    public function search(Request $request): JsonResponse
    {
        $data = $this->permissionCategoryRepository->searchPermissionCategory($request->search);

        return PermissionCategoryResource::collection($data)->additional([
            'message' => 'Permission category list fetched successfully',
        ])->response()->setStatusCode(200);
    }
}
