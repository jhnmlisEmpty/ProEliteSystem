<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();
        $products = Product::all();
        $services = Service::all();
        
        if ($orders->isEmpty()) {
            $this->command->warn('No orders found. Please run OrderSeeder first.');
            return;
        }

        foreach ($orders as $order) {
            $itemCount = rand(1, 4); // 1-4 items per order
            $orderTotal = 0;
            
            for ($i = 0; $i < $itemCount; $i++) {
                // Randomly choose between product or service
                if (rand(0, 1) && $products->isNotEmpty()) {
                    // Add product
                    $product = $products->random();
                    $quantity = rand(1, 3);
                    $price = $product->sell_price ?? 0;
                    $totalPrice = $price * $quantity;
                    
                    if ($price > 0) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'service_id' => null,
                            'quantity' => $quantity,
                            'unit_price' => $price,
                            'total_price' => $totalPrice,
                            'created_at' => $order->created_at,
                            'updated_at' => $order->created_at,
                        ]);
                        
                        $orderTotal += $totalPrice;
                    }
                } elseif ($services->isNotEmpty()) {
                    // Add service
                    $service = $services->random();
                    $quantity = 1;
                    $price = $service->price ?? 0;
                    $totalPrice = $price;
                    
                    if ($price > 0) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => null,
                            'service_id' => $service->id,
                            'quantity' => $quantity,
                            'unit_price' => $price,
                            'total_price' => $totalPrice,
                            'created_at' => $order->created_at,
                            'updated_at' => $order->created_at,
                        ]);
                        
                        $orderTotal += $totalPrice;
                    }
                }
            }
            
            // Update order total
            $order->update(['total_amount' => $orderTotal]);
        }

        $this->command->info('Created ' . OrderItem::count() . ' order items.');
    }
}
