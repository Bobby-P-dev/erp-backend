<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\PermissionResource;
use App\Repositories\Core\PermissionRepository;
use App\Services\Core\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected PermissionRepository $permissionRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $data = $this->permissionRepository->all($request->search, $perPage);

        return PermissionResource::collection($data)->additional([
            'message' => 'Permission list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $data = $this->permissionRepository->searchPermission($request->search);

        return PermissionResource::collection($data)->additional([
            'message' => 'Permission list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function store(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'label' => 'required',
            'category_id' => 'required|exists:permission_categories,id',
        ]);

        $permission = $this->permissionService->store($validate);

        return (new PermissionResource($permission))->additional([
            'message' => 'Permission created successfully',
        ])->response()->setStatusCode(201);
    }

    public function show(string $id): JsonResponse
    {
        $permission = $this->permissionRepository->find($id);

        if (! $permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        $permission->load('permissionCategory:id,name');

        return (new PermissionResource($permission))->additional([
            'message' => 'Permission details fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validate = $request->validate([
            'label' => 'sometimes|required',
            'category_id' => 'sometimes|required|exists:permission_categories,id',
        ]);

        $permission = $this->permissionService->update($validate, $id);

        return (new PermissionResource($permission))->additional([
            'message' => 'Permission updated successfully',
        ])->response()->setStatusCode(200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->permissionRepository->delete($id);

        return response()->json([
            'message' => 'Permission deleted successfully',
        ], 200);
    }
}
