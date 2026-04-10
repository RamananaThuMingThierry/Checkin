<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\ApproveLeaveRequestRequest;
use App\Http\Requests\TenantAdmin\ListPlannedAbsenceCalendarRequest;
use App\Http\Requests\TenantAdmin\ListLeaveRequestRequest;
use App\Http\Requests\TenantAdmin\RejectLeaveRequestRequest;
use App\Http\Requests\TenantAdmin\StoreLeaveRequestRequest;
use App\Services\LeaveRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveRequestService $leaveRequestService)
    {
    }

    public function index(ListLeaveRequestRequest $request, string $tenant)
    {
        $data = $request->validated();
        $leaveRequests = $this->leaveRequestService->listLeaveRequests(
            $tenant,
            $data['status'] ?? null,
            $data['date_from'] ?? null,
            $data['date_to'] ?? null,
            $data['employee_id'] ?? null,
        );

        return response()->json([
            'data' => $leaveRequests,
            'success' => true,
        ], 200);
    }

    public function calendar(ListPlannedAbsenceCalendarRequest $request, string $tenant)
    {
        $data = $request->validated();
        $calendar = $this->leaveRequestService->listPlannedAbsences(
            $tenant,
            $data['date_from'],
            $data['date_to'],
            $data['branch_id'] ?? null,
            $data['department_id'] ?? null,
            $data['employee_id'] ?? null,
        );

        return response()->json([
            'data' => $calendar,
            'success' => true,
        ], 200);
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

    public function approve(ApproveLeaveRequestRequest $request, string $leaveRequest)
    {
        try {
            DB::beginTransaction();
            $approvedLeaveRequest = $this->leaveRequestService->approveLeaveRequest($leaveRequest, $request->user());
            DB::commit();

            return response()->json([
                'data' => $approvedLeaveRequest,
                'message' => 'Leave request approved successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reject(RejectLeaveRequestRequest $request, string $leaveRequest)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $rejectedLeaveRequest = $this->leaveRequestService->rejectLeaveRequest($leaveRequest, $data['rejection_reason']);
            DB::commit();

            return response()->json([
                'data' => $rejectedLeaveRequest,
                'message' => 'Leave request rejected successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
