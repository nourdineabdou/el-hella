<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/distributor/dashboard')->assertRedirect(route('login'));
    }

    public function test_distributor_cannot_access_admin_dashboard(): void
    {
        $distributor = User::factory()->create();

        $this->actingAs($distributor)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_cannot_access_distributor_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/distributor/dashboard')
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_distributor_can_access_distributor_dashboard(): void
    {
        $distributor = User::factory()->create();

        $this->actingAs($distributor)
            ->get('/distributor/dashboard')
            ->assertOk();
    }
}
