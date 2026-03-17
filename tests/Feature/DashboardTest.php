<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_auth(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_dashboard_displays_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $tariff = Tariff::factory()->create();
        Account::factory()->create([
            'user_id' => $user->id,
            'tariff_id' => $tariff->id,
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertOk()
            ->assertSee('Realtime Asterisk')
            ->assertSee('Active Calls');
    }
}
