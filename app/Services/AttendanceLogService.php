<?php

namespace App\Services;

use App\Interfaces\AttendanceLogInterface;
use App\Interfaces\AttendanceRecordInterface;
use App\Interfaces\DeviceInterface;
use App\Interfaces\EmployeeInterface;
use App\Interfaces\EmployeeShiftAssignmentInterface;
use App\Interfaces\LeaveRequestInterface;
use App\Models\AttendanceLog;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\LeaveRequest;
use App\Models\WorkShift;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AttendanceLogService
{
    public function __construct(
        private readonly AttendanceLogInterface $attendanceLogRepository,
        private readonly AttendanceRecordInterface $attendanceRecordRepository,
        private readonly DeviceInterface $deviceRepository,
        private readonly EmployeeInterface $employeeRepository,
        private readonly EmployeeShiftAssignmentInterface $employeeShiftAssignmentRepository,
        private readonly LeaveRequestInterface $leaveRequestRepository,
    ) {
    }

    public function listDailyAttendanceLogs(int $tenantId, string $date)
    {
        return $this->attendanceLogRepository->getDailyLogs(
            tenantId: $tenantId,
            date: $date,
            relations: ['device', 'employee'],
        );
    }

    public function listDailyAttendanceRecords(int $tenantId, string $date, ?int $branchId = null, ?int $departmentId = null)
    {
        $records = collect($this->attendanceRecordRepository->getAll(
            keys: ['tenant_id', 'attendance_date'],
            value: [$tenantId, $date],
            relations: ['employee.department', 'branch', 'workShift'],
            orderBy: ['check_in_time' => 'asc', 'id' => 'asc'],
        ));

        if ($branchId !== null) {
            $records = $records->where('branch_id', $branchId);
        }

        if ($departmentId !== null) {
            $records = $records->filter(fn (AttendanceRecord $record) => $record->employee?->department_id === $departmentId);
        }

        return $records->values();
    }

    public function listAttendanceReport(int $tenantId, string $dateFrom, string $dateTo, ?int $branchId = null, ?int $departmentId = null): Collection
    {
        $employees = collect($this->employeeRepository->getAll(
            keys: ['tenant_id', 'status'],
            value: [$tenantId, 'active'],
            relations: ['department', 'branch'],
            orderBy: ['id' => 'asc'],
        ));

        if ($branchId !== null) {
            $employees = $employees->where('branch_id', $branchId);
        }

        if ($departmentId !== null) {
            $employees = $employees->where('department_id', $departmentId);
        }

        $period = collect(CarbonPeriod::create($dateFrom, $dateTo))
            ->map(fn ($date) => $date->format('Y-m-d'));
        $approvedLeaves = $this->mapApprovedLeaves($tenantId, $dateFrom, $dateTo);

        $report = collect();

        foreach ($employees as $employee) {
            foreach ($period as $date) {
                $record = $this->attendanceRecordRepository->getByKeys(
                    ['tenant_id', 'employee_id', 'attendance_date'],
                    [$tenantId, $employee->id, $date],
                    relations: ['employee.department', 'branch', 'workShift'],
                );

                if ($record !== null) {
                    if ($branchId !== null && (int) $record->branch_id !== $branchId) {
                        continue;
                    }

                    if ($departmentId !== null && (int) ($record->employee?->department_id ?? 0) !== $departmentId) {
                        continue;
                    }

                    if ((int) $record->late_minutes > 0) {
                        $report->push($this->formatLateReportItem($record));
                    }

                    continue;
                }

                if (isset($approvedLeaves[$employee->id][$date])) {
                    $report->push($this->formatApprovedLeaveReportItem($tenantId, $employee, $date, $approvedLeaves[$employee->id][$date]));
                    continue;
                }

                $report->push($this->formatAbsenceReportItem($tenantId, $employee, $date));
            }
        }

        return $report->values();
    }

    public function exportAttendanceReportCsv(int $tenantId, string $dateFrom, string $dateTo, ?int $branchId = null, ?int $departmentId = null): string
    {
        $report = $this->listAttendanceReport($tenantId, $dateFrom, $dateTo, $branchId, $departmentId);
        $stream = fopen('php://temp', 'r+');

        fputcsv($stream, [
            'attendance_date',
            'type',
            'employee_code',
            'employee_name',
            'branch_name',
            'department_name',
            'late_minutes',
            'worked_minutes',
            'status',
        ]);

        foreach ($report as $item) {
            $employee = $item['employee'] ?? null;
            $branch = $item['branch'] ?? null;
            $department = $employee?->department;
            $employeeName = trim(implode(' ', array_filter([
                $employee?->first_name,
                $employee?->last_name,
            ])));

            fputcsv($stream, [
                $item['attendance_date'] ?? null,
                $item['type'] ?? null,
                $employee?->employee_code,
                $employeeName,
                $branch?->name,
                $department?->name,
                $item['late_minutes'] ?? 0,
                $item['worked_minutes'] ?? 0,
                $item['status'] ?? null,
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $csv;
    }

    public function listAttendanceAnomalies(int $tenantId, string $date)
    {
        $records = collect($this->attendanceRecordRepository->getAll(
            keys: ['tenant_id', 'attendance_date'],
            value: [$tenantId, $date],
            relations: ['employee', 'workShift'],
            orderBy: ['employee_id' => 'asc'],
        ));

        return $records
            ->flatMap(fn (AttendanceRecord $record) => $this->extractAnomalies($record))
            ->values();
    }

    public function consolidateDailyAttendance(int $tenantId, string $date)
    {
        $logs = collect($this->attendanceLogRepository->getDailyLogs($tenantId, $date, relations: ['employee']));

        $eligibleLogs = $logs
            ->where('result', 'success')
            ->filter(fn (AttendanceLog $log) => $log->employee_id !== null)
            ->groupBy('employee_id');

        $records = collect();

        foreach ($eligibleLogs as $employeeLogs) {
            $records->push($this->buildAttendanceRecord($tenantId, $date, $employeeLogs->sortBy('scanned_at')->values()));
        }

        return $records->values();
    }

    public function createAttendanceLog(array $data): AttendanceLog
    {
        $payload = Arr::only($data, [
            'device_id',
            'badge_uid',
            'scan_type',
            'scanned_at',
            'latitude',
            'longitude',
        ]);

        $device = $this->deviceRepository->getById((int) $payload['device_id']);

        if ($device === null) {
            throw ValidationException::withMessages([
                'device_id' => 'The selected device is invalid.',
            ]);
        }

        if ($device->status !== 'active') {
            throw ValidationException::withMessages([
                'device_id' => 'The selected device is inactive.',
            ]);
        }

        if ($device->branch_id === null) {
            throw ValidationException::withMessages([
                'device_id' => 'The selected device is not assigned to a branch.',
            ]);
        }

        $payload['tenant_id'] = $device->tenant_id;
        $payload['branch_id'] = $device->branch_id;
        $payload['employee_id'] = null;
        $payload['result'] = 'success';
        $payload['message'] = null;

        return $this->attendanceLogRepository->create($payload);
    }

    public function resolveEmployee(int $attendanceLogId, ?string $employeeIdentifier = null): AttendanceLog
    {
        $attendanceLog = $this->attendanceLogRepository->getById($attendanceLogId);

        if ($attendanceLog === null) {
            throw ValidationException::withMessages([
                'attendance_log_id' => 'The selected attendance log is invalid.',
            ]);
        }

        $identifier = $employeeIdentifier ?? $attendanceLog->badge_uid;

        if ($identifier === null || $identifier === '') {
            throw ValidationException::withMessages([
                'employee_identifier' => 'An employee identifier is required to resolve the attendance log.',
            ]);
        }

        $employee = $this->employeeRepository->getByKeys(
            ['tenant_id', 'badge_uid'],
            [$attendanceLog->tenant_id, $identifier],
        );

        if ($employee === null) {
            $employee = $this->employeeRepository->getByKeys(
                ['tenant_id', 'employee_code'],
                [$attendanceLog->tenant_id, $identifier],
            );
        }

        if ($employee === null) {
            throw ValidationException::withMessages([
                'employee_identifier' => 'The employee could not be resolved from the provided identifier.',
            ]);
        }

        return $this->attendanceLogRepository->update($attendanceLog, [
            'employee_id' => $employee->id,
        ]);
    }

    public function rejectAttendanceLog(int $attendanceLogId, string $reason, ?string $message = null): AttendanceLog
    {
        $attendanceLog = $this->attendanceLogRepository->getById($attendanceLogId);

        if ($attendanceLog === null) {
            throw ValidationException::withMessages([
                'attendance_log_id' => 'The selected attendance log is invalid.',
            ]);
        }

        $result = match ($reason) {
            'unauthorized_device' => 'unauthorized',
            'duplicate_scan' => 'duplicate',
            default => 'failed',
        };

        $defaultMessage = match ($reason) {
            'invalid_scan' => 'The attendance log has been rejected as invalid.',
            'unresolved_employee' => 'The attendance log has been rejected because no employee could be resolved.',
            'unauthorized_device' => 'The attendance log has been rejected because the device is unauthorized.',
            'duplicate_scan' => 'The attendance log has been rejected because it is a duplicate scan.',
        };

        return $this->attendanceLogRepository->update($attendanceLog, [
            'result' => $result,
            'message' => $message ?? $defaultMessage,
        ]);
    }

    private function buildAttendanceRecord(int $tenantId, string $date, Collection $logs): AttendanceRecord
    {
        $firstLog = $logs->first();
        $employeeId = (int) $firstLog->employee_id;
        $branchId = $firstLog->branch_id;
        $workShiftAssignment = $this->resolveAssignment($tenantId, $employeeId, $date);
        $workShift = $workShiftAssignment?->workShift;

        $checkInLog = $logs->first(fn (AttendanceLog $log) => $log->scan_type === 'in');
        $checkOutLog = $logs->reverse()->first(fn (AttendanceLog $log) => $log->scan_type === 'out');

        $checkInTime = $checkInLog?->scanned_at;
        $checkOutTime = $checkOutLog?->scanned_at;
        $breakMinutes = $this->calculateBreakMinutes($logs);
        $workedMinutes = $this->calculateWorkedMinutes($checkInTime, $checkOutTime, $breakMinutes);
        $lateMinutes = $this->calculateLateMinutes($checkInTime, $workShift, $date);
        $overtimeMinutes = $this->calculateOvertimeMinutes($workedMinutes, $workShift);
        [$status, $notes] = $this->resolveStatusAndNotes($checkInTime, $checkOutTime, $lateMinutes, $logs, $workShift);

        $payload = [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'employee_id' => $employeeId,
            'work_shift_id' => $workShift?->id,
            'attendance_date' => $date,
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'worked_minutes' => $workedMinutes,
            'break_minutes' => $breakMinutes,
            'late_minutes' => $lateMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'status' => $status,
            'notes' => $notes,
        ];

        $existing = $this->attendanceRecordRepository->getByKeys(['tenant_id', 'employee_id', 'attendance_date'], [$tenantId, $employeeId, $date]);

        if ($existing !== null) {
            return $this->attendanceRecordRepository->update($existing, $payload);
        }

        return $this->attendanceRecordRepository->create($payload);
    }

    private function resolveAssignment(int $tenantId, int $employeeId, string $date): ?EmployeeShiftAssignment
    {
        $assignments = collect($this->employeeShiftAssignmentRepository->getAll(
            keys: ['tenant_id', 'employee_id'],
            value: [$tenantId, $employeeId],
            relations: ['workShift'],
            orderBy: ['start_date' => 'desc', 'id' => 'desc'],
        ));

        return $assignments->first(function (EmployeeShiftAssignment $assignment) use ($date) {
            if ($assignment->start_date > $date) {
                return false;
            }

            return $assignment->end_date === null || $assignment->end_date >= $date;
        });
    }

    private function calculateBreakMinutes(Collection $logs): int
    {
        $breakStarts = [];
        $minutes = 0;

        foreach ($logs as $log) {
            if ($log->scan_type === 'break_start') {
                $breakStarts[] = Carbon::parse($log->scanned_at);
                continue;
            }

            if ($log->scan_type === 'break_end' && !empty($breakStarts)) {
                $start = array_shift($breakStarts);
                $end = Carbon::parse($log->scanned_at);

                if ($end->greaterThan($start)) {
                    $minutes += $start->diffInMinutes($end);
                }
            }
        }

        return $minutes;
    }

    private function calculateWorkedMinutes(mixed $checkInTime, mixed $checkOutTime, int $breakMinutes): int
    {
        if ($checkInTime === null || $checkOutTime === null) {
            return 0;
        }

        $checkIn = Carbon::parse($checkInTime);
        $checkOut = Carbon::parse($checkOutTime);

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return 0;
        }

        return max(0, $checkIn->diffInMinutes($checkOut) - $breakMinutes);
    }

    private function calculateLateMinutes(mixed $checkInTime, ?WorkShift $workShift, string $date): int
    {
        if ($checkInTime === null || $workShift === null) {
            return 0;
        }

        $scheduledStart = Carbon::parse($date.' '.$workShift->start_time);
        $actualCheckIn = Carbon::parse($checkInTime);
        $tolerance = (int) ($workShift->late_tolerance_minutes ?? 0);

        if ($actualCheckIn->lessThanOrEqualTo($scheduledStart->copy()->addMinutes($tolerance))) {
            return 0;
        }

        return $scheduledStart->diffInMinutes($actualCheckIn);
    }

    private function calculateOvertimeMinutes(int $workedMinutes, ?WorkShift $workShift): int
    {
        if ($workedMinutes === 0 || $workShift === null) {
            return 0;
        }

        $scheduledMinutes = Carbon::parse($workShift->start_time)->diffInMinutes(Carbon::parse($workShift->end_time), false);

        if ($workShift->is_night_shift && $scheduledMinutes < 0) {
            $scheduledMinutes += 1440;
        }

        $scheduledMinutes = max(0, $scheduledMinutes - (int) ($workShift->break_duration_minutes ?? 0));

        return max(0, $workedMinutes - $scheduledMinutes);
    }

    private function resolveStatusAndNotes(mixed $checkInTime, mixed $checkOutTime, int $lateMinutes, Collection $logs, ?WorkShift $workShift): array
    {
        $notes = [];

        if ($checkInTime === null || $checkOutTime === null) {
            $notes[] = 'Incomplete check-in/check-out sequence.';
        }

        $breakStarts = $logs->where('scan_type', 'break_start')->count();
        $breakEnds = $logs->where('scan_type', 'break_end')->count();

        if ($breakStarts !== $breakEnds) {
            $notes[] = 'Unbalanced break scans detected.';
        }

        if ($workShift === null) {
            $notes[] = 'No work shift assignment found for this date.';
        }

        if ($checkInTime === null || $checkOutTime === null) {
            return ['incomplete', implode(' ', $notes) ?: null];
        }

        return [$lateMinutes > 0 ? 'late' : 'present', implode(' ', $notes) ?: null];
    }

    private function extractAnomalies(AttendanceRecord $record): array
    {
        $anomalies = [];

        if ($record->status === 'incomplete') {
            $anomalies[] = $this->formatAnomaly($record, 'missing_checkout', 'Incomplete attendance sequence detected.');
        }

        if ($record->late_minutes > 0) {
            $anomalies[] = $this->formatAnomaly($record, 'late_arrival', 'Employee arrived late.', [
                'late_minutes' => $record->late_minutes,
            ]);
        }

        if ($record->notes !== null && str_contains($record->notes, 'Unbalanced break scans detected.')) {
            $anomalies[] = $this->formatAnomaly($record, 'unbalanced_breaks', 'Break scans are unbalanced.');
        }

        if ($record->notes !== null && str_contains($record->notes, 'No work shift assignment found for this date.')) {
            $anomalies[] = $this->formatAnomaly($record, 'missing_shift_assignment', 'No work shift assignment found for the consolidated date.');
        }

        return $anomalies;
    }

    private function formatAnomaly(AttendanceRecord $record, string $type, string $message, array $extra = []): array
    {
        return array_merge([
            'tenant_id' => $record->tenant_id,
            'attendance_record_id' => $record->id,
            'employee_id' => $record->employee_id,
            'attendance_date' => $record->attendance_date?->format('Y-m-d'),
            'type' => $type,
            'message' => $message,
            'status' => $record->status,
        ], $extra);
    }

    private function formatLateReportItem(AttendanceRecord $record): array
    {
        return [
            'tenant_id' => $record->tenant_id,
            'employee_id' => $record->employee_id,
            'employee' => $record->employee,
            'branch' => $record->branch,
            'attendance_date' => $record->getRawOriginal('attendance_date'),
            'type' => 'late',
            'status' => $record->status,
            'late_minutes' => $record->late_minutes,
            'worked_minutes' => $record->worked_minutes,
            'attendance_record_id' => $record->id,
        ];
    }

    private function formatAbsenceReportItem(int $tenantId, Employee $employee, string $date): array
    {
        return [
            'tenant_id' => $tenantId,
            'employee_id' => $employee->id,
            'employee' => $employee,
            'branch' => $employee->branch,
            'attendance_date' => $date,
            'type' => 'absence',
            'status' => 'absent',
            'late_minutes' => 0,
            'worked_minutes' => 0,
            'attendance_record_id' => null,
        ];
    }

    private function formatApprovedLeaveReportItem(int $tenantId, Employee $employee, string $date, LeaveRequest $leaveRequest): array
    {
        return [
            'tenant_id' => $tenantId,
            'employee_id' => $employee->id,
            'employee' => $employee,
            'branch' => $employee->branch,
            'attendance_date' => $date,
            'type' => 'approved_leave',
            'status' => 'approved',
            'late_minutes' => 0,
            'worked_minutes' => 0,
            'attendance_record_id' => null,
            'leave_request_id' => $leaveRequest->id,
            'leave_type' => $leaveRequest->leaveType,
        ];
    }

    private function mapApprovedLeaves(int $tenantId, string $dateFrom, string $dateTo): array
    {
        $approvedLeaves = collect($this->leaveRequestRepository->getAll(
            keys: ['tenant_id', 'status'],
            value: [$tenantId, 'approved'],
            relations: ['leaveType'],
            orderBy: ['start_date' => 'asc', 'id' => 'asc'],
        ))->filter(function (LeaveRequest $leaveRequest) use ($dateFrom, $dateTo) {
            return $leaveRequest->end_date?->format('Y-m-d') >= $dateFrom
                && $leaveRequest->start_date?->format('Y-m-d') <= $dateTo;
        });

        $mappedLeaves = [];

        foreach ($approvedLeaves as $leaveRequest) {
            $overlapStart = max($dateFrom, $leaveRequest->start_date?->format('Y-m-d'));
            $overlapEnd = min($dateTo, $leaveRequest->end_date?->format('Y-m-d'));

            foreach (CarbonPeriod::create($overlapStart, $overlapEnd) as $date) {
                $mappedLeaves[$leaveRequest->employee_id][$date->format('Y-m-d')] = $leaveRequest;
            }
        }

        return $mappedLeaves;
    }
}
