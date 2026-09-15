<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\AccountingCategoryResource;
use App\Repositories\Core\AccountingCategoryRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingCategoryController extends Controller
{
    public function __construct(
        protected AccountingCategoryRepository $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->all($request->search, $request->only('is_active'));

        return AccountingCategoryResource::collection($data)->additional([
            'message' => 'Accounting category list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $data = $this->repository->search($request->search);

        return AccountingCategoryResource::collection($data)->additional([
            'message' => 'Accounting category search fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:accounting_categories,code',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $category = $this->repository->create($validated);

        return (new AccountingCategoryResource($category))->additional([
            'message' => 'Accounting category created successfully',
        ])->response()->setStatusCode(201);
    }

    public function show(string $id): JsonResponse
    {
        $category = $this->repository->find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Accounting category not found',
            ], 404);
        }

        return (new AccountingCategoryResource($category))->additional([
            'message' => 'Accounting category details fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = $this->repository->find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Accounting category not found',
            ], 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:50|unique:accounting_categories,code,'.$id,
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $updated = $this->repository->update($validated, $id);

        return (new AccountingCategoryResource($updated))->additional([
            'message' => 'Accounting category updated successfully',
        ])->response()->setStatusCode(200);
    }

    public function destroy(string $id): JsonResponse
    {
        $category = $this->repository->find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Accounting category not found',
            ], 404);
        }

        try {
            $this->repository->delete($id);

            return response()->json([
                'message' => 'Accounting category deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1451) {
                return response()->json([
                    'message' => 'Cannot delete Accounting category because it is in use.',
                ], 409);
            }
            throw $e;
        }
    }
}
