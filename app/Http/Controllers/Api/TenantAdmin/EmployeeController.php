<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\StoreEmployeeRequest;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService)
    {
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $employee = $this->employeeService->createEmployee($data);

            DB::commit();

            return response()->json([
                'data' => $employee,
                'message' => 'Employee created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
