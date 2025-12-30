<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Customer;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        
        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run CustomerSeeder first.');
            return;
        }

        $vehicleTypes = ['Van', 'Truck', 'SUV', 'Sedan', 'Pickup'];
        $statuses = ['pending', 'in_progress', 'completed'];
        $paymentStatuses = ['unpaid', 'partial', 'paid'];
        
        // Create orders for the past 30 days
        for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
            $ordersPerDay = rand(1, 5); // 1-5 orders per day
            
            for ($i = 0; $i < $ordersPerDay; $i++) {
                $customer = $customers->random();
                $createdAt = Carbon::today()->subDays($daysAgo)->addHours(rand(8, 18))->addMinutes(rand(0, 59));
                
                Order::create([
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'vehicle_type' => $vehicleTypes[array_rand($vehicleTypes)],
                    'plate_number' => strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3)) . '-' . rand(1000, 9999),
                    'type' => rand(0, 1) ? 'service' : 'product',
                    'status' => $daysAgo > 5 ? 'completed' : $statuses[array_rand($statuses)],
                    'payment_status' => $daysAgo > 10 ? 'paid' : $paymentStatuses[array_rand($paymentStatuses)],
                    'total_amount' => 0, // Will be updated by OrderItemSeeder
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        $this->command->info('Created ' . Order::count() . ' orders.');
    }
}
