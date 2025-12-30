<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobOrder;
use App\Models\Order;

class JobOrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::where('type', 'service')->get();
        
        if ($orders->isEmpty()) {
            $this->command->warn('No service orders found.');
            return;
        }

        $statuses = ['pending', 'in_progress', 'completed'];

        foreach ($orders as $order) {
            // Determine status based on order status
            $status = $order->status;
            if ($order->status === 'completed') {
                $status = 'completed';
            } elseif ($order->status === 'in_progress') {
                $status = rand(0, 1) ? 'in_progress' : 'pending';
            } else {
                $status = 'pending';
            }

            JobOrder::create([
                'order_id' => $order->id,
                'status' => $status,
                'start_date' => $order->created_at,
                'end_date' => $status === 'completed' ? $order->created_at->addHours(rand(2, 48)) : null,
                'notes' => rand(0, 1) ? 'Customer requested express service' : null,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
        }

        $this->command->info('Created ' . JobOrder::count() . ' job orders.');
    }
}
