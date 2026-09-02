<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Services\Core\UserService;
use Illuminate\Http\Request;

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

        return response()->json([
            'message' => 'Users fetched successfully',
            'data' => $data
        ], 200);
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
            'data' => $user
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
            'user' => $user,
        ]);
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user = $this->userService->updatePassword($id, $request->string('password'));

        return response()->json([
            'message' => 'User password updated successfully',
            'data' => $user
        ], 200);
    }
}
