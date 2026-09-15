<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\AccountingSubcategoryResource;
use App\Repositories\Core\AccountingSubcategoryRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingSubcategoryController extends Controller
{
    public function __construct(
        protected AccountingSubcategoryRepository $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->all(
            $request->search,
            $request->only(['is_active', 'accounting_category_id'])
        );

        return AccountingSubcategoryResource::collection($data)->additional([
            'message' => 'Accounting subcategory list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $data = $this->repository->search(
            $request->search,
            $request->query('accounting_category_id')
        );

        return AccountingSubcategoryResource::collection($data)->additional([
            'message' => 'Accounting subcategory search fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'accounting_category_id' => 'required|integer|exists:accounting_categories,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $subcategory = $this->repository->create($validated);
        $subcategory->load('category');

        return (new AccountingSubcategoryResource($subcategory))->additional([
            'message' => 'Accounting subcategory created successfully',
        ])->response()->setStatusCode(201);
    }

    public function show(string $id): JsonResponse
    {
        $subcategory = $this->repository->find($id);

        if (! $subcategory) {
            return response()->json([
                'message' => 'Accounting subcategory not found',
            ], 404);
        }

        return (new AccountingSubcategoryResource($subcategory))->additional([
            'message' => 'Accounting subcategory details fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $subcategory = $this->repository->find($id);

        if (! $subcategory) {
            return response()->json([
                'message' => 'Accounting subcategory not found',
            ], 404);
        }

        $validated = $request->validate([
            'accounting_category_id' => 'sometimes|required|integer|exists:accounting_categories,id',
            'code' => 'sometimes|required|string|max:50',
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $updated = $this->repository->update($validated, $id);
        $updated?->load('category');

        return (new AccountingSubcategoryResource($updated))->additional([
            'message' => 'Accounting subcategory updated successfully',
        ])->response()->setStatusCode(200);
    }

    public function destroy(string $id): JsonResponse
    {
        $subcategory = $this->repository->find($id);

        if (! $subcategory) {
            return response()->json([
                'message' => 'Accounting subcategory not found',
            ], 404);
        }

        try {
            $this->repository->delete($id);

            return response()->json([
                'message' => 'Accounting subcategory deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1451) {
                return response()->json([
                    'message' => 'Cannot delete Accounting subcategory because it is in use.',
                ], 409);
            }
            throw $e;
        }
    }
}
