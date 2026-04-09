<?php

namespace Tests\Feature\Api;

use App\Models\Module;
use App\Models\Offer;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class AttachModulesToOfferTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_attaches_a_module_to_an_offer(): void
    {
        $offer = Offer::query()->create([
            'name' => 'Starter',
            'code' => 'starter',
        ]);

        $module = Module::query()->create([
            'name' => 'Attendance',
            'code' => 'attendance',
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/v1/super-admin/offers/{$offer->id}/modules", [
            'module_id' => $module->id,
            'is_included' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $offer->id)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('offer_modules', [
            'offer_id' => $offer->id,
            'module_id' => $module->id,
            'is_included' => true,
        ]);
    }

    public function test_it_does_not_duplicate_the_same_module_on_an_offer(): void
    {
        $offer = Offer::query()->create([
            'name' => 'Starter',
            'code' => 'starter',
        ]);

        $module = Module::query()->create([
            'name' => 'Attendance',
            'code' => 'attendance',
            'is_active' => true,
        ]);

        $this->postJson("/api/v1/super-admin/offers/{$offer->id}/modules", [
            'module_id' => $module->id,
        ])->assertOk();

        $response = $this->postJson("/api/v1/super-admin/offers/{$offer->id}/modules", [
            'module_id' => $module->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['module_id']);
    }

    public function test_it_tracks_the_inclusion_flag_on_the_relation(): void
    {
        $offer = Offer::query()->create([
            'name' => 'Starter',
            'code' => 'starter',
        ]);

        $module = Module::query()->create([
            'name' => 'Attendance',
            'code' => 'attendance',
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/v1/super-admin/offers/{$offer->id}/modules", [
            'module_id' => $module->id,
            'is_included' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $offer->id)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('offer_modules', [
            'offer_id' => $offer->id,
            'module_id' => $module->id,
            'is_included' => false,
        ]);
    }
}
