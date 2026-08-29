<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\EmployeeResource;
use App\Services\Core\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    protected EmployeeService $employeeService;

    public function __construct()
    {
        $this->employeeService = new EmployeeService();
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->employeeService->getAll($request->search, $request->filter ?? []);

        return EmployeeResource::collection($data)->additional([
            'message' => 'Employee list fetched successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'nik' => [
                'required',
                Rule::unique('employees')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })
            ],
            'company_id' => 'required|exists:companies,id',
            'division_id' => 'required|exists:divisions,id',
            'position_id' => 'required|exists:positions,id',
            'email' => 'nullable|email',
            'is_active' => 'nullable|boolean',
        ]);

        $employee = $this->employeeService->create($request->all());

        return response()->json([
            'message' => 'Employee created successfully',
            'data' => $employee
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
