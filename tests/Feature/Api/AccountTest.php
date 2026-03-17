<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithAccount(): User
    {
        $user = User::factory()->create();
        $tariff = Tariff::factory()->create();
        Account::factory()->create([
            'user_id' => $user->id,
            'tariff_id' => $tariff->id,
            'number' => '1001',
            'balance' => 500.00,
        ]);
        return $user;
    }

    public function test_show_returns_account_with_tariff(): void
    {
        $user = $this->createUserWithAccount();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/account');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'number', 'name', 'balance', 'status', 'tariff' => ['id', 'name', 'price_per_minute']],
            ]);
    }

    public function test_balance_returns_current_balance(): void
    {
        $user = $this->createUserWithAccount();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/account/balance');

        $response->assertOk()
            ->assertJsonFragment(['balance' => 500.0]);
    }

    public function test_account_requires_auth(): void
    {
        $this->getJson('/api/account')->assertStatus(401);
        $this->getJson('/api/account/balance')->assertStatus(401);
    }
}
