<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\StoreEmployeeShiftAssignmentRequest;
use App\Services\EmployeeShiftAssignmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeShiftAssignmentController extends Controller
{
    public function __construct(private readonly EmployeeShiftAssignmentService $assignmentService)
    {
    }

    public function store(StoreEmployeeShiftAssignmentRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $assignment = $this->assignmentService->createAssignment($data);

            DB::commit();

            return response()->json([
                'data' => $assignment,
                'message' => 'Employee work shift assignment created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
