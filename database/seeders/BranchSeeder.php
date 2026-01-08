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
        // Create Main Branch
        $mainBranch = Branch::create([
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'address' => '123 Main Street, Metro Manila',
            'phone' => '+63 912 345 6789',
            'is_active' => true,
        ]);

        // Create Branch 2
        $branch2 = Branch::create([
            'name' => 'Quezon City Branch',
            'code' => 'QC01',
            'address' => '456 Commonwealth Ave, Quezon City',
            'phone' => '+63 912 345 6790',
            'is_active' => true,
        ]);

        // Create Branch 3
        $branch3 = Branch::create([
            'name' => 'Makati Branch',
            'code' => 'MKT01',
            'address' => '789 Ayala Avenue, Makati City',
            'phone' => '+63 912 345 6791',
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
            'name' => 'Main Branch Manager',
            'email' => 'manager.main@proelite.com',
            'password' => Hash::make('password'),
            'branch_id' => $mainBranch->id,
            'role' => 'manager',
        ]);

        // Create User for Main Branch
        User::create([
            'name' => 'Main Branch User',
            'email' => 'user.main@proelite.com',
            'password' => Hash::make('password'),
            'branch_id' => $mainBranch->id,
            'role' => 'user',
        ]);

        // Create User for QC Branch
        User::create([
            'name' => 'QC Branch User',
            'email' => 'user.qc@proelite.com',
            'password' => Hash::make('password'),
            'branch_id' => $branch2->id,
            'role' => 'user',
        ]);

        // Create User for Makati Branch
        User::create([
            'name' => 'Makati Branch User',
            'email' => 'user.makati@proelite.com',
            'password' => Hash::make('password'),
            'branch_id' => $branch3->id,
            'role' => 'user',
        ]);

        $this->command->info('✓ Branches and users created successfully!');
        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('Admin: admin@proelite.com / password');
        $this->command->info('Main Manager: manager.main@proelite.com / password');
        $this->command->info('Main User: user.main@proelite.com / password');
        $this->command->info('QC User: user.qc@proelite.com / password');
        $this->command->info('Makati User: user.makati@proelite.com / password');
    }
}
