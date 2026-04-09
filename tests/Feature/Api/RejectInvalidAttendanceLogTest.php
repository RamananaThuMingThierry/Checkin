<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class RejectInvalidAttendanceLogTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_marks_an_unresolved_scan_as_failed_with_traceable_message(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $attendanceLog = $this->createAttendanceLog($tenant, $branch, 'BADGE-001');

        $response = $this->postJson("/api/v1/attendance-logs/{$attendanceLog->id}/reject", [
            'reason' => 'unresolved_employee',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.result', 'failed')
            ->assertJsonPath('data.message', 'The attendance log has been rejected because no employee could be resolved.')
            ->assertJsonPath('success', true);
    }

    public function test_it_marks_an_unauthorized_scan_with_the_unauthorized_result(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $attendanceLog = $this->createAttendanceLog($tenant, $branch, 'BADGE-001');

        $response = $this->postJson("/api/v1/attendance-logs/{$attendanceLog->id}/reject", [
            'reason' => 'unauthorized_device',
            'message' => 'Device key mismatch detected.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.result', 'unauthorized')
            ->assertJsonPath('data.message', 'Device key mismatch detected.')
            ->assertJsonPath('success', true);
    }

    public function test_it_marks_a_duplicate_scan_explicitly(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $attendanceLog = $this->createAttendanceLog($tenant, $branch, 'BADGE-001');

        $response = $this->postJson("/api/v1/attendance-logs/{$attendanceLog->id}/reject", [
            'reason' => 'duplicate_scan',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.result', 'duplicate')
            ->assertJsonPath('success', true);
    }

    public function test_it_rejects_an_unknown_attendance_log(): void
    {
        $response = $this->postJson('/api/v1/attendance-logs/999999/reject', [
            'reason' => 'invalid_scan',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['attendance_log_id']);
    }

    public function test_it_validates_the_rejection_reason(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $attendanceLog = $this->createAttendanceLog($tenant, $branch, 'BADGE-001');

        $response = $this->postJson("/api/v1/attendance-logs/{$attendanceLog->id}/reject", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    }

    private function createTenant(string $code): Tenant
    {
        return Tenant::query()->create([
            'name' => strtoupper($code),
            'code' => $code,
            'status' => 'trial',
            'currency' => 'MGA',
        ]);
    }

    private function createBranch(Tenant $tenant, string $code): Branch
    {
        return Branch::query()->create([
            'tenant_id' => $tenant->id,
            'name' => strtoupper($code),
            'code' => $code,
            'status' => 'active',
            'is_main' => $code === 'hq',
        ]);
    }

    private function createAttendanceLog(Tenant $tenant, Branch $branch, string $badgeUid): AttendanceLog
    {
        $device = Device::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'FRONT-DESK-01',
            'code' => 'front-desk-01-'.uniqid(),
            'type' => 'terminal',
            'status' => 'active',
        ]);

        return AttendanceLog::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'device_id' => $device->id,
            'badge_uid' => $badgeUid,
            'scan_type' => 'in',
            'scanned_at' => '2026-04-09 08:00:00',
            'result' => 'success',
        ]);
    }
}
