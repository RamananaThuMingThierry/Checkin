<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class CreateAttendanceLogTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_raw_attendance_log_from_an_assigned_active_device(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $device = $this->createDevice($tenant, $branch, 'front-desk-01');

        $response = $this->postJson('/api/v1/attendance-logs', [
            'device_id' => $device->id,
            'badge_uid' => 'BADGE-001',
            'scan_type' => 'in',
            'scanned_at' => '2026-04-09 08:00:00',
            'latitude' => -18.8792000,
            'longitude' => 47.5079000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.branch_id', $branch->id)
            ->assertJsonPath('data.device_id', $device->id)
            ->assertJsonPath('data.employee_id', null)
            ->assertJsonPath('data.badge_uid', 'BADGE-001')
            ->assertJsonPath('data.scan_type', 'in')
            ->assertJsonPath('data.result', 'success')
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('attendance_logs', [
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'device_id' => $device->id,
            'badge_uid' => 'BADGE-001',
            'scan_type' => 'in',
            'result' => 'success',
        ]);
    }

    public function test_it_rejects_an_unknown_device_source(): void
    {
        $response = $this->postJson('/api/v1/attendance-logs', [
            'device_id' => 999999,
            'badge_uid' => 'BADGE-001',
            'scan_type' => 'in',
            'scanned_at' => '2026-04-09 08:00:00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id']);
    }

    public function test_it_rejects_an_inactive_device_source(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $device = $this->createDevice($tenant, $branch, 'front-desk-01', 'inactive');

        $response = $this->postJson('/api/v1/attendance-logs', [
            'device_id' => $device->id,
            'badge_uid' => 'BADGE-001',
            'scan_type' => 'in',
            'scanned_at' => '2026-04-09 08:00:00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id']);
    }

    public function test_it_rejects_a_device_without_branch_assignment(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $device = Device::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'FRONT-DESK-01',
            'code' => 'front-desk-01',
            'type' => 'terminal',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/attendance-logs', [
            'device_id' => $device->id,
            'badge_uid' => 'BADGE-001',
            'scan_type' => 'in',
            'scanned_at' => '2026-04-09 08:00:00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id']);
    }

    public function test_it_validates_the_minimum_raw_scan_payload(): void
    {
        $response = $this->postJson('/api/v1/attendance-logs', [
            'device_id' => 'invalid',
            'scan_type' => 'unknown',
            'scanned_at' => 'not-a-date',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id', 'badge_uid', 'scan_type', 'scanned_at']);
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

    private function createDevice(Tenant $tenant, Branch $branch, string $code, string $status = 'active'): Device
    {
        return Device::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => strtoupper($code),
            'code' => $code,
            'type' => 'terminal',
            'status' => $status,
        ]);
    }
}
