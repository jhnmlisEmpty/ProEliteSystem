<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Branch 1
        $branch1 = Branch::create([
            'name' => 'Baguio City Branch',
            'code' => 'BG01',
            'address' => 'Baguio City',
            'phone' => '09266530192',
            'is_active' => true,
        ]);

        // Create Branch 2
        $branch2 = Branch::create([
            'name' => 'NCR Branch',
            'code' => 'NCR01',
            'address' => 'Brgy. Calumbaya, Bauang Sur',
            'phone' => '09569424297',
            'is_active' => true,
        ]);

        // Create Admin User (can access all branches)
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@proelite.com',
            'password' => Hash::make('password'),
            'branch_id' => null, // Admin doesn't need a specific branch
            'role' => 'admin',
        ]);

        // Create Manager for Main Branch
        User::create([
            'name' => 'Manager',
            'email' => 'manager@proelite.com',
            'password' => Hash::make('password'),
            'branch_id' => null,
            'role' => 'manager',
        ]);

        // Create User for Main Branch
        User::create([
            'name' => 'Baguio Branch',
            'email' => 'user.baguio@proelite.com',
            'password' => Hash::make('password'),
            'branch_id' => $branch1->id,
            'role' => 'user',
        ]);

        // Create User for NCR Branch
        User::create([
            'name' => 'NCR Branch',
            'email' => 'user.ncr@proelite.com',
            'password' => Hash::make('password'),
            'branch_id' => $branch2->id,
            'role' => 'user',
        ]);


        $this->command->info('✓ Branches and users created successfully!');
    }
}
