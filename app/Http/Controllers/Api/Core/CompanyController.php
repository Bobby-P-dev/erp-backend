<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\CompanyResource;
use App\Services\Core\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(
        protected CompanyService $companyService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->companyService->getAll($request->search);

        return CompanyResource::collection($data)->additional([
            'message' => 'Company list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|unique:companies,code',
            'name' => 'required',
            'is_active' => 'nullable|boolean',
        ]);

        $company = $this->companyService->store($data);

        return (new CompanyResource($company))->additional([
            'message' => 'Company created successfully',
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'code' => 'sometimes|required|unique:companies,code,'.$id,
            'name' => 'sometimes|required',
            'is_active' => 'nullable|boolean',
        ]);

        $company = $this->companyService->update($data, $id);

        return (new CompanyResource($company))->additional([
            'message' => 'Company updated successfully',
        ])->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->companyService->delete($id);

        return response()->json([
            'message' => 'Company deleted successfully',
        ], 200);
    }

    public function searchCompany(Request $request): JsonResponse
    {
        $data = $this->companyService->searchCompany($request->search);

        return CompanyResource::collection($data)->additional([
            'message' => 'Company list fetched successfully',
        ])->response()->setStatusCode(200);
    }
}
