<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Resources\Core\UserResource;

use App\Http\Controllers\Controller;
use App\Services\Core\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function index(Request $request)
    {
        $data = $this->userService->getAll($request->search, $request->filter ?? []);

        return UserResource::collection($data)->additional([
            'message' => 'Users fetched successfully'
        ])->response()->setStatusCode(200);
    }

    public function syncRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $user = $this->userService->syncRoles($id, $request->roles);

        return response()->json([
            'message' => 'Roles synced successfully',
            'data' => new UserResource($user)
        ], 200);
    }

    public function getMe(Request $request)
    {
        $user = $request->user();

        $user->load('roles');

        $user->all_permissions = $user
            ->getAllPermissions()
            ->pluck('name');

        return response()->json([
            'message' => 'Authenticated user fetched successfully',
            'user' => new UserResource($user),
        ]);
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $this->userService->updatePassword($id, $request->string('password'));

        return response()->json([
            'message' => 'User password updated successfully',
            'data' => $user
        ], 200);
    }
}
