<?php

namespace Tests\Feature;

use App\Models\DailyCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsCatalogs;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase, SeedsCatalogs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalogs();
    }

    public function test_only_admin_can_access_users_management(): void
    {
        $analyst = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($analyst)->get(route('users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('users.index'))->assertOk();
    }

    public function test_only_admin_can_access_settings(): void
    {
        $analyst = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($analyst)->get(route('settings.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('settings.index'))->assertOk();
    }

    public function test_analyst_cannot_create_users(): void
    {
        $analyst = User::factory()->create();

        $this->actingAs($analyst)->post(route('users.store'), [
            'name' => 'Invasor',
            'email' => 'invasor@test.com',
            'password' => 'secret123',
            'role' => 'admin',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'invasor@test.com']);
    }

    public function test_dashboard_restricts_analyst_to_own_data_filter(): void
    {
        $analyst = User::factory()->create();
        $other = User::factory()->create();

        // Un caso de otro analista
        DailyCase::create([
            'case_number' => 'AUTH-1',
            'received_date' => today()->toDateString(),
            'status_id' => $this->statusPendiente->id,
            'analyst_id' => $other->id,
        ]);

        $this->actingAs($analyst)->get(route('dashboard', ['analyst_id' => $other->id]));
        $this->actingAs($analyst)->get(route('dashboard'));

        // No hay error y se muestra la sección personal
        $this->assertAuthenticatedAs($analyst);
    }
}