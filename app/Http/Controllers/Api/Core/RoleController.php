<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\RoleResource;
use App\Http\Resources\Core\RoleShowResource;
use App\Repositories\Core\RoleRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleRepository $roleRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->roleRepository->all($request->search);

        return RoleResource::collection($data)->additional([
            'message' => 'Role list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $data = $this->roleRepository->searchRole($request->search);

        return RoleResource::collection($data)->additional([
            'message' => 'Role search fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function store(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $validate['guard_name'] = 'web';

        $role = $this->roleRepository->create($validate);

        return (new RoleResource($role))->additional([
            'message' => 'Role created successfully',
        ])->response()->setStatusCode(201);
    }

    public function show(string $id): JsonResponse
    {
        $role = $this->roleRepository->find($id);

        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return (new RoleShowResource($role))->additional([
            'message' => 'Role details fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validate = $request->validate([
            'name' => 'required|unique:roles,name,'.$id,
        ]);

        $this->roleRepository->update($validate, $id);

        $role = $this->roleRepository->find($id);

        return (new RoleResource($role))->additional([
            'message' => 'Role updated successfully',
        ])->response()->setStatusCode(200);
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->roleRepository->delete($id);

            return response()->json([
                'message' => 'Role deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'message' => 'Cannot delete role because it is in use.',
                ], 409);
            }
            throw $e;
        }
    }

    public function syncPermissions(Request $request, $id): JsonResponse
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = $this->roleRepository->syncPermissions($id, $request->permissions);

        return (new RoleResource($role))->additional([
            'message' => 'Permissions synced successfully',
        ])->response()->setStatusCode(200);
    }
}
