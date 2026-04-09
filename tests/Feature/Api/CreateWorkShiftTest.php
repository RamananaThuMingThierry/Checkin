<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\WorkShift;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class CreateWorkShiftTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_work_shift(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');

        $response = $this->postJson('/api/v1/work-shifts', [
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Morning Shift',
            'code' => 'morning',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'break_duration_minutes' => 60,
            'late_tolerance_minutes' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.branch_id', $branch->id)
            ->assertJsonPath('data.name', 'Morning Shift')
            ->assertJsonPath('data.code', 'morning')
            ->assertJsonPath('data.is_night_shift', false)
            ->assertJsonPath('success', true);
    }

    public function test_work_shift_code_must_be_unique_within_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');

        WorkShift::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Morning Shift',
            'code' => 'morning',
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $response = $this->postJson('/api/v1/work-shifts', [
            'tenant_id' => $tenant->id,
            'name' => 'Office Shift',
            'code' => 'morning',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_branch_must_belong_to_work_shift_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $foreignBranch = $this->createBranch($otherTenant, 'foreign');

        $response = $this->postJson('/api/v1/work-shifts', [
            'tenant_id' => $tenant->id,
            'branch_id' => $foreignBranch->id,
            'name' => 'Morning Shift',
            'code' => 'morning',
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    }

    public function test_day_shift_rejects_end_time_before_start_time(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->postJson('/api/v1/work-shifts', [
            'tenant_id' => $tenant->id,
            'name' => 'Broken Shift',
            'code' => 'broken',
            'start_time' => '17:00',
            'end_time' => '08:00',
            'is_night_shift' => false,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['end_time']);
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
}
