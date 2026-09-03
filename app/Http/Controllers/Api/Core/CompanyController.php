<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Resources\Core\CompanyResource;

use App\Http\Controllers\Controller;
use App\Repositories\Core\CompanyRepository;
use App\Services\Core\CompanyService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected CompanyService $companyService;
    protected CompanyRepository $companyRepository;

    public function __construct()
    {
        $this->companyService = new CompanyService();
        $this->companyRepository = new CompanyRepository();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->companyService->getAll($request->search);

        return CompanyResource::collection($data)->additional([
            'message' => 'Company list fetched successfully'
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|unique:companies,code',
            'name' => 'required',
            'is_active' => 'nullable|boolean',
        ]);

        $company = $this->companyService->store($data);

        return response()->json([
            'message' => 'Company created successfully',
            'data' => new CompanyResource($company)
        ], 201);
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
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'code' => 'sometimes|required|unique:companies,code,' . $id,
            'name' => 'sometimes|required',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }
        if (isset($data['name'])) {
            $data['name'] = strtoupper($data['name']);
        }

        $company = $this->companyRepository->update($data, $id);

        return response()->json([
            'message' => 'Company updated successfully',
            'data' => new CompanyResource($company)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->companyRepository->delete($id);

        return response()->json([
            'message' => 'Company deleted successfully'
        ], 200);
    }

    public function searchCompany(Request $request)
    {
        $data = $this->companyRepository->searchCompany($request->search);

        return CompanyResource::collection($data)->additional([
            'message' => 'Company list fetched successfully'
        ])->response()->setStatusCode(200);
    }
}
