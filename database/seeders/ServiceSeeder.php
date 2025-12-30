<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Custom Paint Job', 'base_labor_cost' => 15000],
            ['name' => 'Full Vehicle Wrap', 'base_labor_cost' => 12000],
            ['name' => 'Upholstery Installation', 'base_labor_cost' => 8000],
            ['name' => 'Leather Seat Installation', 'base_labor_cost' => 10000],
            ['name' => 'Carpet Installation', 'base_labor_cost' => 3000],
            ['name' => 'Dashboard Wrapping', 'base_labor_cost' => 4000],
            ['name' => 'Roof Lining Installation', 'base_labor_cost' => 3500],
            ['name' => 'Window Tinting', 'base_labor_cost' => 2500],
            ['name' => 'Sound System Installation', 'base_labor_cost' => 5000],
            ['name' => 'LED Light Installation', 'base_labor_cost' => 3000],
            ['name' => 'Bull Bar Installation', 'base_labor_cost' => 2000],
            ['name' => 'Roof Rack Installation', 'base_labor_cost' => 2500],
            ['name' => 'Side Step Installation', 'base_labor_cost' => 1500],
            ['name' => 'Wheel Upgrade & Installation', 'base_labor_cost' => 2000],
            ['name' => 'Suspension Modification', 'base_labor_cost' => 6000],
            ['name' => 'Custom Bumper Installation', 'base_labor_cost' => 4000],
            ['name' => 'Tail Light Modification', 'base_labor_cost' => 2500],
            ['name' => 'Headlight Restoration', 'base_labor_cost' => 1500],
            ['name' => 'Interior Detailing', 'base_labor_cost' => 3000],
            ['name' => 'Exterior Detailing', 'base_labor_cost' => 2500],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $this->command->info('Successfully seeded ' . count($services) . ' services!');
    }
}
