<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\ConsolidateAttendanceRequest;
use App\Http\Requests\TenantAdmin\ExportAttendanceReportRequest;
use App\Http\Requests\TenantAdmin\ListAttendanceAnomalyRequest;
use App\Http\Requests\TenantAdmin\ListAttendanceLogRequest;
use App\Http\Requests\TenantAdmin\ListAttendanceRecordRequest;
use App\Http\Requests\TenantAdmin\ListAttendanceReportRequest;
use App\Http\Requests\TenantAdmin\RejectAttendanceLogRequest;
use App\Http\Requests\TenantAdmin\ResolveAttendanceLogEmployeeRequest;
use App\Http\Requests\TenantAdmin\StoreAttendanceLogRequest;
use App\Services\AttendanceLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceLogController extends Controller
{
    public function __construct(private readonly AttendanceLogService $attendanceLogService)
    {
    }

    public function index(ListAttendanceLogRequest $request, int $tenant)
    {
        $data = $request->validated();
        $attendanceLogs = $this->attendanceLogService->listDailyAttendanceLogs($tenant, $data['date']);

        return response()->json([
            'data' => $attendanceLogs,
            'success' => true,
        ], 200);
    }

    public function listRecords(ListAttendanceRecordRequest $request, int $tenant)
    {
        $data = $request->validated();
        $attendanceRecords = $this->attendanceLogService->listDailyAttendanceRecords(
            $tenant,
            $data['date'],
            $data['branch_id'] ?? null,
            $data['department_id'] ?? null,
        );

        return response()->json([
            'data' => $attendanceRecords,
            'success' => true,
        ], 200);
    }

    public function listReport(ListAttendanceReportRequest $request, int $tenant)
    {
        $data = $request->validated();
        $report = $this->attendanceLogService->listAttendanceReport(
            $tenant,
            $data['date_from'],
            $data['date_to'],
            $data['branch_id'] ?? null,
            $data['department_id'] ?? null,
        );

        return response()->json([
            'data' => $report,
            'success' => true,
        ], 200);
    }

    public function exportReport(ExportAttendanceReportRequest $request, string $tenant)
    {
        $tenantId = decrypt_to_int_or_null($tenant);

        if ($tenantId === null) {
            throw ValidationException::withMessages([
                'tenant' => 'The selected tenant is invalid.',
            ]);
        }

        $data = $request->validated();
        $csv = $this->attendanceLogService->exportAttendanceReportCsv(
            $tenantId,
            $data['date_from'],
            $data['date_to'],
            $data['branch_id'] ?? null,
            $data['department_id'] ?? null,
        );

        $fileName = sprintf('attendance-report-%s-%s.csv', $data['date_from'], $data['date_to']);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function listAnomalies(ListAttendanceAnomalyRequest $request, int $tenant)
    {
        $data = $request->validated();
        $anomalies = $this->attendanceLogService->listAttendanceAnomalies($tenant, $data['date']);

        return response()->json([
            'data' => $anomalies,
            'success' => true,
        ], 200);
    }

    public function consolidate(ConsolidateAttendanceRequest $request, int $tenant)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $attendanceRecords = $this->attendanceLogService->consolidateDailyAttendance($tenant, $data['date']);
            DB::commit();

            return response()->json([
                'data' => $attendanceRecords,
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function store(StoreAttendanceLogRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $attendanceLog = $this->attendanceLogService->createAttendanceLog($data);
            DB::commit();

            return response()->json([
                'data' => $attendanceLog,
                'message' => 'Attendance log created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function resolveEmployee(ResolveAttendanceLogEmployeeRequest $request, int $attendanceLog)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $updatedAttendanceLog = $this->attendanceLogService->resolveEmployee($attendanceLog, $data['employee_identifier'] ?? null);
            DB::commit();

            return response()->json([
                'data' => $updatedAttendanceLog,
                'message' => 'Attendance log employee resolved successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reject(RejectAttendanceLogRequest $request, int $attendanceLog)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $updatedAttendanceLog = $this->attendanceLogService->rejectAttendanceLog($attendanceLog, $data['reason'], $data['message'] ?? null);
            DB::commit();

            return response()->json([
                'data' => $updatedAttendanceLog,
                'message' => 'Attendance log rejected successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
