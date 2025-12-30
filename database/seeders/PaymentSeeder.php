<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Order;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();
        
        if ($orders->isEmpty()) {
            $this->command->warn('No orders found. Please run OrderSeeder first.');
            return;
        }

        $paymentMethods = ['cash', 'card', 'bank_transfer', 'check'];

        foreach ($orders as $order) {
            if ($order->payment_status === 'paid') {
                // Full payment
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => 'completed',
                    'reference' => 'PAY-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999),
                    'paid_at' => $order->created_at->addHours(rand(0, 48)),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
                ]);
            } elseif ($order->payment_status === 'partial') {
                // Partial payment (50-80% of total)
                $partialAmount = (int)($order->total_amount * (rand(50, 80) / 100));
                
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $partialAmount,
                    'method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => 'completed',
                    'reference' => 'PAY-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999),
                    'paid_at' => $order->created_at->addHours(rand(0, 24)),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
                ]);
            }
            // No payment for 'unpaid' status
        }

        $this->command->info('Created ' . Payment::count() . ' payments.');
    }
}
