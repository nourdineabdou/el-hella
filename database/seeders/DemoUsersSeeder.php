<?php

namespace Database\Seeders;

use App\Models\Distributor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin.demo@elhella.com'],
            [
                'name' => 'Admin Démo',
                'password' => Hash::make('password'),
                'phone' => '22200000001',
                'role' => 'admin',
                'language' => 'ar',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $distributorUser = User::firstOrCreate(
            ['email' => 'distributeur.demo@elhella.com'],
            [
                'name' => 'Mohamed Lemine',
                'password' => Hash::make('password'),
                'phone' => '22200000002',
                'role' => 'distributor',
                'language' => 'ar',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $distributorUser->hasRole('distributor')) {
            $distributorUser->assignRole('distributor');
        }

        Distributor::firstOrCreate(
            ['user_id' => $distributorUser->id],
            [
                'code' => 'DIST-001',
                'phone' => '22200000002',
                'zone' => 'Tevragh-Zeina',
                'region' => 'Nouakchott',
                'is_active' => true,
            ]
        );
    }
}
