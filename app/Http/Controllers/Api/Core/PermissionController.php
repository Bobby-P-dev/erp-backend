<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Resources\Core\PermissionResource;

use App\Http\Controllers\Controller;
use App\Services\Core\PermissionService;
use App\Repositories\Core\PermissionRepository;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;
    protected PermissionRepository $permissionRepository;

    public function __construct(PermissionService $permissionService, PermissionRepository $permissionRepository)
    {
        $this->permissionService = $permissionService;
        $this->permissionRepository = $permissionRepository;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $data = $this->permissionRepository->all($request->search, $perPage);

        return PermissionResource::collection($data)->additional([
            'message' => 'Permission list fetched successfully'
        ])->response()->setStatusCode(200);
    }

    public function search(Request $request)
    {
        $data = $this->permissionRepository->searchPermission($request->search);

        return PermissionResource::collection($data)->additional([
            'message' => 'Permission list fetched successfully'
        ])->response()->setStatusCode(200);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'label' => 'required',
            'category_id' => 'required|exists:permission_categories,id',
        ]);

        $permission = $this->permissionService->store($validate);

        return response()->json([
            'message' => 'Permission created successfully',
            'data' => new PermissionResource($permission),
        ], 201);
    }

    public function show(string $id)
    {
        $permission = $this->permissionRepository->find($id);

        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        $permission->load('permissionCategory:id,name');

        return response()->json([
            'message' => 'Permission details fetched successfully',
            'data' => new PermissionResource($permission)
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'label' => 'sometimes|required',
            'category_id' => 'sometimes|required|exists:permission_categories,id',
        ]);

        $permission = $this->permissionService->update($validate, $id);

        return response()->json([
            'message' => 'Permission updated successfully',
            'data' => new PermissionResource($permission)
        ], 200);
    }

    public function destroy(string $id)
    {
        $this->permissionRepository->delete($id);

        return response()->json([
            'message' => 'Permission deleted successfully'
        ], 200);
    }
}
