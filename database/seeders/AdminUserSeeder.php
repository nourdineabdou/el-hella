<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['phone' => '36888222'],
            [
                'name' => 'Mohamed Limam',
                'email' => '36888222@elhella.local',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'language' => 'ar',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
