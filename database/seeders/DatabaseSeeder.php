<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        
        // Seed in order of dependencies
        $this->call([
            // UserSeeder::class,
            ProductSeeder::class,
            ServiceSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            JobOrderSeeder::class,
            PaymentSeeder::class,
            ProductLogSeeder::class,
            EmployeeSeeder::class,
            BranchSeeder::class,
        ]);
        
        $this->command->info('✅ Database seeding completed successfully!');
    }
}

