<?php

namespace Tests\Feature;

use App\Models\Stakeholder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StakeholderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_lookup_stakeholder_by_vkn(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $stakeholder = Stakeholder::query()->create([
            'vkn_tckn' => '1234567890',
            'title' => 'Acme Ltd',
            'country' => 'TR',
            'company_type' => 'Company',
            'status' => 'Active',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson(route('api.stakeholders.lookup', ['vkn' => $stakeholder->vkn_tckn]));

        $response
            ->assertOk()
            ->assertJson([
                'found' => true,
                'data' => [
                    'id' => $stakeholder->id,
                    'title' => 'Acme Ltd',
                    'vkn_tckn' => '1234567890',
                ],
            ]);
    }

    public function test_lookup_returns_not_found_for_unknown_vkn(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this
            ->actingAs($user)
            ->getJson(route('api.stakeholders.lookup', ['vkn' => '0000000000']));

        $response
            ->assertOk()
            ->assertJson([
                'found' => false,
            ]);
    }

    public function test_admin_can_quick_create_stakeholder(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('api.stakeholders.quick'), [
                'vkn_tckn' => '9988776655',
                'title' => 'Quick Stakeholder',
            ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Quick Stakeholder',
                    'vkn_tckn' => '9988776655',
                ],
            ]);

        $this->assertDatabaseHas('stakeholders', [
            'vkn_tckn' => '9988776655',
            'title' => 'Quick Stakeholder',
            'created_by' => $user->id,
        ]);
    }
}