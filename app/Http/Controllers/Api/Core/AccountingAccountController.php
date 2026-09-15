<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\AccountingAccountResource;
use App\Repositories\Core\AccountingAccountRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingAccountController extends Controller
{
    public function __construct(
        protected AccountingAccountRepository $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->all(
            $request->search,
            $request->only(['is_active', 'accounting_subcategory_id'])
        );

        return AccountingAccountResource::collection($data)->additional([
            'message' => 'Accounting account list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $data = $this->repository->search(
            $request->search,
            $request->query('accounting_subcategory_id')
        );

        return AccountingAccountResource::collection($data)->additional([
            'message' => 'Accounting account search fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'accounting_subcategory_id' => 'required|integer|exists:accounting_subcategories,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $account = $this->repository->create($validated);
        $account->load('subcategory');

        return (new AccountingAccountResource($account))->additional([
            'message' => 'Accounting account created successfully',
        ])->response()->setStatusCode(201);
    }

    public function show(string $id): JsonResponse
    {
        $account = $this->repository->find($id);

        if (! $account) {
            return response()->json([
                'message' => 'Accounting account not found',
            ], 404);
        }

        return (new AccountingAccountResource($account))->additional([
            'message' => 'Accounting account details fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $account = $this->repository->find($id);

        if (! $account) {
            return response()->json([
                'message' => 'Accounting account not found',
            ], 404);
        }

        $validated = $request->validate([
            'accounting_subcategory_id' => 'sometimes|required|integer|exists:accounting_subcategories,id',
            'code' => 'sometimes|required|string|max:50',
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $updated = $this->repository->update($validated, $id);
        $updated?->load('subcategory');

        return (new AccountingAccountResource($updated))->additional([
            'message' => 'Accounting account updated successfully',
        ])->response()->setStatusCode(200);
    }

    public function destroy(string $id): JsonResponse
    {
        $account = $this->repository->find($id);

        if (! $account) {
            return response()->json([
                'message' => 'Accounting account not found',
            ], 404);
        }

        try {
            $this->repository->delete($id);

            return response()->json([
                'message' => 'Accounting account deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1451) {
                return response()->json([
                    'message' => 'Cannot delete Accounting account because it is in use.',
                ], 409);
            }
            throw $e;
        }
    }
}
