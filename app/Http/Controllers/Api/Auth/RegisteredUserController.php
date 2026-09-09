<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'account_type' => ['nullable', 'string', 'in:employee,supplier'],
            'employee_id' => ['required_without:supplier_id', 'prohibits:supplier_id', 'nullable', 'exists:employees,id', 'unique:users,employee_id'],
            'supplier_id' => ['required_without:employee_id', 'prohibits:employee_id', 'nullable', 'exists:suppliers,id', 'unique:users,supplier_id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $accountType = $request->input('account_type')
            ?? ($request->filled('supplier_id') ? 'supplier' : 'employee');

        $user = User::create([
            'employee_id' => $request->employee_id,
            'supplier_id' => $request->supplier_id,
            'account_type' => $accountType,
            'password' => Hash::make($request->string('password')),
        ]);

        $user->load(['employee', 'supplier']);

        event(new Registered($user));

        return response()->json([
            'message' => 'User account created successfully',
            'data' => $user,
        ], 201);
    }
}
