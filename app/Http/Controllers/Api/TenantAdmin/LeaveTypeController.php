<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\StoreLeaveTypeRequest;
use App\Services\LeaveTypeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveTypeController extends Controller
{
    public function __construct(private readonly LeaveTypeService $leaveTypeService)
    {
    }

    public function index(int $tenant)
    {
        $leaveTypes = $this->leaveTypeService->listLeaveTypes($tenant);

        return response()->json([
            'data' => $leaveTypes,
            'success' => true,
        ], 200);
    }

    public function store(StoreLeaveTypeRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $leaveType = $this->leaveTypeService->createLeaveType($data);
            DB::commit();

            return response()->json([
                'data' => $leaveType,
                'message' => 'Leave type created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
