<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Cdr;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_returns_active_calls(): void
    {
        $user = User::factory()->create();
        $tariff = Tariff::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'tariff_id' => $tariff->id,
        ]);

        Cdr::factory()->create([
            'account_id' => $account->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/calls/active');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_active_returns_empty_when_no_calls(): void
    {
        $user = User::factory()->create();
        $tariff = Tariff::factory()->create();
        Account::factory()->create([
            'user_id' => $user->id,
            'tariff_id' => $tariff->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/calls/active');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_active_requires_auth(): void
    {
        $this->getJson('/api/calls/active')->assertStatus(401);
    }
}
