<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\StoreLeaveRequestRequest;
use App\Services\LeaveRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveRequestService $leaveRequestService)
    {
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $leaveRequest = $this->leaveRequestService->createLeaveRequest($data);
            DB::commit();

            return response()->json([
                'data' => $leaveRequest,
                'message' => 'Leave request created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
