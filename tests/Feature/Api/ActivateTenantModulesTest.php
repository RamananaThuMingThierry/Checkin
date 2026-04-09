<?php

namespace Tests\Feature\Api;

use App\Models\Module;
use App\Models\Offer;
use App\Models\Subscription;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ActivateTenantModulesTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_activates_the_included_modules_for_a_tenant_subscription(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $attendanceModule = $this->createModule('Attendance', 'attendance');
        $payrollModule = $this->createModule('Payroll', 'payroll');

        $offer->modules()->attach($attendanceModule->id, ['is_included' => true]);
        $offer->modules()->attach($payrollModule->id, ['is_included' => false]);

        $subscription = $this->createSubscription($tenant, $offer);

        $response = $this->postJson("/api/v1/super-admin/subscriptions/{$subscription->id}/activate-modules");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tenant_id', $tenant->id)
            ->assertJsonPath('data.0.module_id', $attendanceModule->id)
            ->assertJsonPath('data.0.is_enabled', true)
            ->assertJsonPath('success', true);
    }

    public function test_it_does_not_duplicate_already_activated_modules(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $attendanceModule = $this->createModule('Attendance', 'attendance');

        $offer->modules()->attach($attendanceModule->id, ['is_included' => true]);
        $subscription = $this->createSubscription($tenant, $offer);

        $this->postJson("/api/v1/super-admin/subscriptions/{$subscription->id}/activate-modules")->assertOk();
        $response = $this->postJson("/api/v1/super-admin/subscriptions/{$subscription->id}/activate-modules");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('tenant_modules', 1);
    }

    public function test_it_lists_the_active_modules_of_a_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $attendanceModule = $this->createModule('Attendance', 'attendance');

        $offer->modules()->attach($attendanceModule->id, ['is_included' => true]);
        $subscription = $this->createSubscription($tenant, $offer);
        $this->postJson("/api/v1/super-admin/subscriptions/{$subscription->id}/activate-modules")->assertOk();

        $response = $this->getJson("/api/v1/super-admin/tenants/{$tenant->id}/modules");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.module.code', 'attendance')
            ->assertJsonPath('success', true);
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

    private function createOffer(): Offer
    {
        return Offer::query()->create([
            'name' => 'Starter',
            'code' => 'starter',
            'monthly_price' => 25000,
            'yearly_price' => 250000,
            'currency' => 'MGA',
            'is_active' => true,
        ]);
    }

    private function createModule(string $name, string $code): Module
    {
        return Module::query()->create([
            'name' => $name,
            'code' => $code,
            'is_active' => true,
        ]);
    }

    private function createSubscription(Tenant $tenant, Offer $offer): Subscription
    {
        return Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'offer_id' => $offer->id,
            'subscription_number' => 'SUB-TEST-001-'.uniqid(),
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'starts_at' => '2026-04-09',
            'next_billing_date' => '2026-05-09',
            'base_amount' => 25000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 25000,
            'currency' => 'MGA',
        ]);
    }
}
