<?php

namespace Tests\Feature;

use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_distributor_roles_are_created_with_expected_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = Role::findByName('admin');
        $distributor = Role::findByName('distributor');

        $this->assertTrue($admin->hasPermissionTo('users.delete'));
        $this->assertTrue($admin->hasPermissionTo('settings.manage'));

        $this->assertTrue($distributor->hasPermissionTo('visits.create'));
        $this->assertTrue($distributor->hasPermissionTo('distributions.create'));
        $this->assertFalse($distributor->hasPermissionTo('users.delete'));
        $this->assertFalse($distributor->hasPermissionTo('settings.manage'));
    }

    public function test_admin_user_seeder_creates_default_admin_account(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@elhella.com',
            'role' => 'admin',
        ]);
    }
}
