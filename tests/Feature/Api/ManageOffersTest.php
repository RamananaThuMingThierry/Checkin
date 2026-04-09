<?php

namespace Tests\Feature\Api;

use App\Models\Offer;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ManageOffersTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_an_offer_with_name_code_and_pricing(): void
    {
        $response = $this->postJson('/api/v1/super-admin/offers', [
            'name' => 'Starter',
            'code' => 'starter',
            'monthly_price' => 49.99,
            'yearly_price' => 499.99,
            'currency' => 'MGA',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Starter')
            ->assertJsonPath('data.code', 'starter')
            ->assertJsonPath('data.monthly_price', '49.99')
            ->assertJsonPath('data.yearly_price', '499.99')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('offers', [
            'name' => 'Starter',
            'code' => 'starter',
            'monthly_price' => 49.99,
            'yearly_price' => 499.99,
            'currency' => 'MGA',
            'is_active' => true,
        ]);
    }

    public function test_it_prevents_duplicate_offer_codes(): void
    {
        Offer::query()->create([
            'name' => 'Starter',
            'code' => 'starter',
        ]);

        $response = $this->postJson('/api/v1/super-admin/offers', [
            'name' => 'Starter Plus',
            'code' => 'starter',
            'monthly_price' => 79.99,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_it_creates_an_inactive_offer(): void
    {
        $response = $this->postJson('/api/v1/super-admin/offers', [
            'name' => 'Legacy',
            'code' => 'legacy',
            'monthly_price' => 10,
            'is_active' => false,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.code', 'legacy')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('offers', [
            'code' => 'legacy',
            'is_active' => false,
        ]);
    }
}
