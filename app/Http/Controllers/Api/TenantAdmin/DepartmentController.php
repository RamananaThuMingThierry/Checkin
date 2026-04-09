<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\StoreDepartmentRequest;
use App\Http\Requests\TenantAdmin\UpdateDepartmentRequest;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $departmentService)
    {
    }

    public function index(int $tenant)
    {
        $departments = $this->departmentService->listDepartments($tenant);

        return response()->json([
            'data' => $departments,
            'success' => true,
        ], 200);
    }

    public function store(StoreDepartmentRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $department = $this->departmentService->createDepartment($data);

            DB::commit();

            return response()->json([
                'data' => $department,
                'message' => 'Department created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(UpdateDepartmentRequest $request, int $department)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $updatedDepartment = $this->departmentService->updateDepartment($department, $data);

            DB::commit();

            return response()->json([
                'data' => $updatedDepartment,
                'message' => 'Department updated successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
