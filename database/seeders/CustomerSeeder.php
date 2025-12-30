<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Juan Dela Cruz', 'phone' => '0917-123-4567', 'address' => '123 Sampaguita St, Makati City'],
            ['name' => 'Maria Santos', 'phone' => '0918-234-5678', 'address' => '456 Mabini Ave, Quezon City'],
            ['name' => 'Carlos Reyes', 'phone' => '0920-345-6789', 'address' => '789 Bonifacio St, Pasig City'],
            ['name' => 'Ana Villanueva', 'phone' => '0917-456-7890', 'address' => '12 Ayala Blvd, Manila'],
            ['name' => 'Mark Johnson', 'phone' => '0917-567-8901', 'address' => '34 Ortigas Ave, Pasig City'],
            ['name' => 'Grace Lim', 'phone' => '0917-678-9012', 'address' => '56 Katipunan Rd, Quezon City'],
            ['name' => 'Pedro Garcia', 'phone' => '0917-789-0123', 'address' => '78 Taft Ave, Manila'],
            ['name' => 'Liza Cruz', 'phone' => '0917-890-1234', 'address' => '90 EDSA, Mandaluyong City'],
            ['name' => 'Robert Lee', 'phone' => '0917-901-2345', 'address' => '101 Banawe St, Quezon City'],
            ['name' => 'Sofia Tan', 'phone' => '0917-012-3456', 'address' => '202 Jupiter St, Makati City'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('Successfully seeded ' . count($customers) . ' customers!');
    }
}
