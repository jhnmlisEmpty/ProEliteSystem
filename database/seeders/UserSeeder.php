<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@proelite.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create staff users
        User::create([
            'name' => 'John Staff',
            'email' => 'john@proelite.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Maria Staff',
            'email' => 'maria@proelite.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}
