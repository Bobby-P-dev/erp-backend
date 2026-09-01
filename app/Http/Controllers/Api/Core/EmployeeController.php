<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\EmployeeStoreRequest;
use App\Http\Requests\Core\EmployeeUpdateRequest;
use App\Http\Resources\Core\EmployeeResource;
use App\Models\Core\DivisionPosition;
use App\Repositories\Core\EmployeeRepository;
use App\Services\Core\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    protected EmployeeService $employeeService;
    protected EmployeeRepository $employeeRepository;

    public function __construct()
    {
        $this->employeeService = new EmployeeService();
        $this->employeeRepository = new EmployeeRepository();
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->employeeService->getAll($request->search, $request->filter ?? []);

        return EmployeeResource::collection($data)->additional([
            'message' => 'Employee list fetched successfully'
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeStoreRequest $request)
    {
        $employee = $this->employeeService->create($request->validated());

        return response()->json([
            'message' => 'Employee created successfully',
            'data' => $employee
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $employee = $this->employeeRepository->show($id);
        return response()->json([
            'message' => 'Employee fetched successfully',
            'data' => new EmployeeResource($employee)
        ], 200);
    }

    public function getMe()
    {
        $employee = $this->employeeRepository->getMe();

        return response()->json([
            'message' => 'Employee fetched successfully',
            'data' => new EmployeeResource($employee)
        ], 200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeUpdateRequest $request, int $id)
    {
        $employee = $this->employeeService->update($request->validated(), $id);

        return response()->json([
            'message' => 'Employee updated successfully',
            'data' => new EmployeeResource($employee)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->employeeRepository->delete($id);

        return response()->json([
            'message' => 'Employee deleted successfully',
        ], 200);
    }

    /**
     * Mengambil data Division dan Position dengan (atau tanpa) pencarian
     */
    public function getDivision(Request $request)
    {
        $data = $this->employeeRepository->getDivision($request->search);

        return response()->json([
            'message' => 'Division fetched successfully',
            'data' => $data
        ]);
    }

    public function getPosition(Request $request, int $divisionId)
    {
        $data = $this->employeeRepository->getPosition($request->search, $divisionId);

        return response()->json([
            'message' => 'Position fetched successfully',
            'data' => $data
        ]);
    }
}
