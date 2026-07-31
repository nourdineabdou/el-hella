<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * The full permission list for EL HELLA.
     *
     * @var array<int, string>
     */
    protected array $permissions = [
        'users.view', 'users.create', 'users.update', 'users.delete',

        'distributors.view', 'distributors.create', 'distributors.update', 'distributors.delete',

        'shops.view', 'shops.create', 'shops.update', 'shops.delete', 'shops.assign',

        'products.view', 'products.create', 'products.update', 'products.delete',

        'visits.view-all', 'visits.view-own', 'visits.create',

        'distributions.view-all', 'distributions.view-own', 'distributions.create',

        'goals.view', 'goals.create', 'goals.update', 'goals.delete',

        'gps-alerts.view', 'gps-alerts.review',

        'reports.view', 'reports.export',

        'map.view', 'settings.manage',
    ];

    /**
     * Permissions granted to the "distributor" role.
     *
     * @var array<int, string>
     */
    protected array $distributorPermissions = [
        'shops.view',
        'visits.view-own', 'visits.create',
        'distributions.view-own', 'distributions.create',
        'goals.view',
    ];

    public function run(): void
    {
        Cache::forget(config('permission.cache.key'));

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($this->permissions);

        $distributor = Role::firstOrCreate(['name' => 'distributor', 'guard_name' => 'web']);
        $distributor->syncPermissions($this->distributorPermissions);

        Cache::forget(config('permission.cache.key'));
    }
}
