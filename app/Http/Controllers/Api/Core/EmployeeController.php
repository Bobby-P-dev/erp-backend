<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\StoreEmployeeRequest;
use App\Http\Requests\Core\UpdateEmployeeRequest;
use App\Http\Resources\Core\EmployeeResource;
use App\Repositories\Core\EmployeeRepository;
use App\Services\Core\EmployeeService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService,
        protected EmployeeRepository $employeeRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->employeeService->getAll($request->search, $request->filter ?? []);

        return EmployeeResource::collection($data)->additional([
            'message' => 'Employee list fetched successfully',
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->create($request->validated());

        return (new EmployeeResource($employee))->additional([
            'message' => 'Employee created successfully',
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $employee = $this->employeeRepository->show($id);

        return (new EmployeeResource($employee))->additional([
            'message' => 'Employee fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function getMe(): JsonResponse
    {
        $employee = $this->employeeRepository->getMe();

        return (new EmployeeResource($employee))->additional([
            'message' => 'Employee fetched successfully',
        ])->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, int $id): JsonResponse
    {
        $employee = $this->employeeService->update($request->validated(), $id);

        return (new EmployeeResource($employee))->additional([
            'message' => 'Employee updated successfully',
        ])->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->employeeRepository->delete($id);

            return response()->json([
                'message' => 'Employee deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'message' => 'Cannot delete employee because it is in use.',
                ], 409);
            }
            throw $e;
        }
    }

    /**
     * Mengambil data Division dan Position dengan (atau tanpa) pencarian
     */
    public function getDivision(Request $request): JsonResponse
    {
        $data = $this->employeeRepository->getDivision($request->search, $request->company_id);

        return EmployeeResource::collection($data)->additional([
            'message' => 'Division fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function getPosition(Request $request, int $divisionId): JsonResponse
    {
        $data = $this->employeeRepository->getPosition($request->search, $divisionId);

        return EmployeeResource::collection($data)->additional([
            'message' => 'Position fetched successfully',
        ])->response()->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $data = $this->employeeRepository->searchEmployee($request->search);

        return EmployeeResource::collection($data)->additional([
            'message' => 'Employee search results fetched successfully',
        ])->response()->setStatusCode(200);
    }
}
