<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Resources\Core\RoleResource;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\RoleShowResource;
use App\Repositories\Core\RoleRepository;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function index(Request $request)
    {
        $data = $this->roleRepository->all($request->search);

        return RoleResource::collection($data)->additional([
            'message' => 'Role list fetched successfully'
        ])->response()->setStatusCode(200);
    }

    public function search(Request $request)
    {
        $data = $this->roleRepository->searchRole($request->search);

        return RoleResource::collection($data)->additional([
            'message' => 'Role search fetched successfully'
        ])->response()->setStatusCode(200);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $validate['guard_name'] = 'web';

        $role = $this->roleRepository->create($validate);

        return response()->json([
            'message' => 'Role created successfully',
            'data' => new RoleResource($role),
        ], 201);
    }

    public function show(string $id)
    {
        $role = $this->roleRepository->find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json([
            'message' => 'Role details fetched successfully',
            'data' => new RoleShowResource($role)
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
        ]);

        $this->roleRepository->update($validate, $id);

        $role = $this->roleRepository->find($id);

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => new RoleResource($role)
        ], 200);
    }

    public function destroy(string $id)
    {
        try {
            $this->roleRepository->delete($id);

            return response()->json([
                'message' => 'Role deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'message' => 'Cannot delete role because it is in use.'
                ], 409);
            }
            throw $e;
        }
    }

    public function syncPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = $this->roleRepository->syncPermissions($id, $request->permissions);

        return response()->json([
            'message' => 'Permissions synced successfully',
            'data' => new RoleResource($role)
        ], 200);
    }
}
