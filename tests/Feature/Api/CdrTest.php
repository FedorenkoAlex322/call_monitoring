<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Cdr;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CdrTest extends TestCase
{
    use RefreshDatabase;

    private function createSetup(): array
    {
        $user = User::factory()->create();
        $tariff = Tariff::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'tariff_id' => $tariff->id,
        ]);
        return [$user, $account];
    }

    public function test_index_returns_paginated_cdrs(): void
    {
        [$user, $account] = $this->createSetup();
        Cdr::factory()->count(3)->create([
            'account_id' => $account->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/cdrs');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_index_excludes_active_cdrs(): void
    {
        [$user, $account] = $this->createSetup();
        Cdr::factory()->create(['account_id' => $account->id, 'status' => 'completed']);
        Cdr::factory()->create(['account_id' => $account->id, 'status' => 'active']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/cdrs');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_show_returns_cdr(): void
    {
        [$user, $account] = $this->createSetup();
        $cdr = Cdr::factory()->create(['account_id' => $account->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/cdrs/{$cdr->id}");

        $response->assertOk()
            ->assertJsonFragment(['uniqueid' => $cdr->uniqueid]);
    }

    public function test_show_returns_403_for_foreign_cdr(): void
    {
        [$user, $account] = $this->createSetup();

        // Create CDR belonging to a different account
        $otherAccount = Account::factory()->create(['tariff_id' => Tariff::factory()]);
        $cdr = Cdr::factory()->create(['account_id' => $otherAccount->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/cdrs/{$cdr->id}");

        $response->assertStatus(403);
    }

    public function test_cdrs_require_auth(): void
    {
        $this->getJson('/api/cdrs')->assertStatus(401);
    }
}
