<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductLog;
use App\Models\Product;
use Carbon\Carbon;

class ProductLogSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        
        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');
            return;
        }

        $reasons = [
            'Purchase order received',
            'Supplier delivery',
            'Stock transfer in',
            'Sales order',
            'Damaged item',
            'Stock transfer out',
            'Physical count adjustment',
            'Inventory correction',
            'System correction',
        ];

        foreach ($products as $product) {
            // Create 3-8 logs per product for the past 30 days
            $logCount = rand(3, 8);
            
            for ($i = 0; $i < $logCount; $i++) {
                $createdAt = Carbon::today()->subDays(rand(0, 30))->addHours(rand(8, 18));
                
                // Randomly determine if it's stock in or out
                $isStockIn = rand(0, 1);
                $quantity = $isStockIn ? rand(10, 50) : -rand(1, 10);
                
                ProductLog::create([
                    'product_id' => $product->id,
                    'change_amount' => $quantity,
                    'reason' => $reasons[array_rand($reasons)],
                    'reference_id' => 'REF-' . rand(10000, 99999),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        $this->command->info('Created ' . ProductLog::count() . ' product logs.');
    }
}
