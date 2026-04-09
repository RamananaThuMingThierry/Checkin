<?php

namespace Tests\Feature\Api;

use App\Models\Module;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ManageModulesCatalogTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_module_in_the_catalog(): void
    {
        $response = $this->postJson('/api/v1/super-admin/modules', [
            'name' => 'Attendance',
            'code' => 'attendance',
            'description' => 'Attendance management module',
            'version' => '1.0.0',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Attendance')
            ->assertJsonPath('data.code', 'attendance')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('success', true);
    }

    public function test_it_lists_modules_from_the_catalog(): void
    {
        Module::query()->create([
            'name' => 'Attendance',
            'code' => 'attendance',
            'is_active' => true,
        ]);

        Module::query()->create([
            'name' => 'Payroll',
            'code' => 'payroll',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/super-admin/modules');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.code', 'attendance')
            ->assertJsonPath('data.1.code', 'payroll')
            ->assertJsonPath('success', true);
    }

    public function test_it_rejects_duplicate_module_codes(): void
    {
        Module::query()->create([
            'name' => 'Attendance',
            'code' => 'attendance',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/super-admin/modules', [
            'name' => 'Attendance v2',
            'code' => 'attendance',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }
}
