<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Alice Reyes',
            'Bruno Santos',
            'Carlo Mendoza',
            'Dana Villanueva',
            'Ely Cruz',
        ];

        foreach ($names as $name) {
            Employee::firstOrCreate(['name' => $name]);
        }
    }
}