<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Retail Products
            [
                'name' => 'Chrome Alloy Wheels 18"',
                'sku' => 'RIM-CHR-18-001',
                'type' => 'retail',
                'stock_qty' => 24.00,
                'buy_price' => 450.00,
                'sell_price' => 750.00,
                'alert_limit' => 8.00,
            ],
            [
                'name' => 'Black Matte Rims 20"',
                'sku' => 'RIM-BLK-20-002',
                'type' => 'retail',
                'stock_qty' => 16.00,
                'buy_price' => 520.00,
                'sell_price' => 850.00,
                'alert_limit' => 6.00,
            ],
            [
                'name' => 'LED Light Bar - 50 inch',
                'sku' => 'LED-BAR-50-003',
                'type' => 'retail',
                'stock_qty' => 12.00,
                'buy_price' => 180.00,
                'sell_price' => 320.00,
                'alert_limit' => 5.00,
            ],
            [
                'name' => 'Bull Bar - Heavy Duty Steel',
                'sku' => 'BUL-STL-HD-004',
                'type' => 'retail',
                'stock_qty' => 8.00,
                'buy_price' => 650.00,
                'sell_price' => 1200.00,
                'alert_limit' => 3.00,
            ],
            [
                'name' => 'Roof Rack - Aluminum',
                'sku' => 'ROF-ALU-STD-005',
                'type' => 'retail',
                'stock_qty' => 15.00,
                'buy_price' => 280.00,
                'sell_price' => 480.00,
                'alert_limit' => 5.00,
            ],
            [
                'name' => 'Side Steps - Chrome',
                'sku' => 'STP-CHR-001-006',
                'type' => 'retail',
                'stock_qty' => 10.00,
                'buy_price' => 320.00,
                'sell_price' => 550.00,
                'alert_limit' => 4.00,
            ],
            [
                'name' => 'Tonneau Cover - Soft Roll',
                'sku' => 'TON-SFT-ROL-007',
                'type' => 'retail',
                'stock_qty' => 6.00,
                'buy_price' => 420.00,
                'sell_price' => 680.00,
                'alert_limit' => 3.00,
            ],
            [
                'name' => 'Winch - 12000 lbs',
                'sku' => 'WNC-12K-ELC-008',
                'type' => 'retail',
                'stock_qty' => 5.00,
                'buy_price' => 580.00,
                'sell_price' => 950.00,
                'alert_limit' => 2.00,
            ],
            [
                'name' => 'Mud Flaps - Universal',
                'sku' => 'MUD-FLP-UNI-009',
                'type' => 'retail',
                'stock_qty' => 30.00,
                'buy_price' => 45.00,
                'sell_price' => 85.00,
                'alert_limit' => 10.00,
            ],
            [
                'name' => 'Tow Hitch - Class III',
                'sku' => 'TOW-HTC-C3-010',
                'type' => 'retail',
                'stock_qty' => 12.00,
                'buy_price' => 180.00,
                'sell_price' => 320.00,
                'alert_limit' => 5.00,
            ],

            // Material Products
            [
                'name' => 'Premium Leather - Black',
                'sku' => 'MAT-LTH-BLK-001',
                'type' => 'material',
                'stock_qty' => 150.50,
                'buy_price' => 25.00,
                'sell_price' => 45.00,
                'alert_limit' => 30.00,
            ],
            [
                'name' => 'Premium Leather - Brown',
                'sku' => 'MAT-LTH-BRN-002',
                'type' => 'material',
                'stock_qty' => 120.75,
                'buy_price' => 25.00,
                'sell_price' => 45.00,
                'alert_limit' => 30.00,
            ],
            [
                'name' => 'Carpet Material - Gray',
                'sku' => 'MAT-CRP-GRY-003',
                'type' => 'material',
                'stock_qty' => 200.00,
                'buy_price' => 12.00,
                'sell_price' => 22.00,
                'alert_limit' => 50.00,
            ],
            [
                'name' => 'Carpet Material - Black',
                'sku' => 'MAT-CRP-BLK-004',
                'type' => 'material',
                'stock_qty' => 180.50,
                'buy_price' => 12.00,
                'sell_price' => 22.00,
                'alert_limit' => 50.00,
            ],
            [
                'name' => 'Vinyl - Automotive Grade',
                'sku' => 'MAT-VNL-AUT-005',
                'type' => 'material',
                'stock_qty' => 95.25,
                'buy_price' => 18.00,
                'sell_price' => 32.00,
                'alert_limit' => 25.00,
            ],
            [
                'name' => 'Foam Padding - 1 inch',
                'sku' => 'MAT-FOM-1IN-006',
                'type' => 'material',
                'stock_qty' => 250.00,
                'buy_price' => 8.50,
                'sell_price' => 15.00,
                'alert_limit' => 60.00,
            ],
            [
                'name' => 'Thread - Heavy Duty Black',
                'sku' => 'MAT-THD-BLK-007',
                'type' => 'material',
                'stock_qty' => 45.00,
                'buy_price' => 5.00,
                'sell_price' => 12.00,
                'alert_limit' => 15.00,
            ],
            [
                'name' => 'Thread - Heavy Duty Beige',
                'sku' => 'MAT-THD-BGE-008',
                'type' => 'material',
                'stock_qty' => 38.00,
                'buy_price' => 5.00,
                'sell_price' => 12.00,
                'alert_limit' => 15.00,
            ],
            [
                'name' => 'Spray Paint - Matte Black',
                'sku' => 'MAT-PNT-MBK-009',
                'type' => 'material',
                'stock_qty' => 24.00,
                'buy_price' => 15.00,
                'sell_price' => 28.00,
                'alert_limit' => 10.00,
            ],
            [
                'name' => 'Spray Paint - Gloss White',
                'sku' => 'MAT-PNT-GWH-010',
                'type' => 'material',
                'stock_qty' => 18.00,
                'buy_price' => 15.00,
                'sell_price' => 28.00,
                'alert_limit' => 10.00,
            ],
            [
                'name' => 'Adhesive Spray - Industrial',
                'sku' => 'MAT-ADH-IND-011',
                'type' => 'material',
                'stock_qty' => 32.00,
                'buy_price' => 22.00,
                'sell_price' => 38.00,
                'alert_limit' => 12.00,
            ],
            [
                'name' => 'Clear Coat - Automotive',
                'sku' => 'MAT-CLR-AUT-012',
                'type' => 'material',
                'stock_qty' => 28.00,
                'buy_price' => 35.00,
                'sell_price' => 58.00,
                'alert_limit' => 10.00,
            ],
            [
                'name' => 'Sandpaper - Assorted Grits',
                'sku' => 'MAT-SND-AST-013',
                'type' => 'material',
                'stock_qty' => 150.00,
                'buy_price' => 2.50,
                'sell_price' => 5.00,
                'alert_limit' => 40.00,
            ],
            [
                'name' => 'Steel Sheet - 4x8 ft',
                'sku' => 'MAT-STL-4X8-014',
                'type' => 'material',
                'stock_qty' => 12.00,
                'buy_price' => 120.00,
                'sell_price' => 180.00,
                'alert_limit' => 4.00,
            ],
            [
                'name' => 'Aluminum Sheet - 4x8 ft',
                'sku' => 'MAT-ALU-4X8-015',
                'type' => 'material',
                'stock_qty' => 8.00,
                'buy_price' => 95.00,
                'sell_price' => 145.00,
                'alert_limit' => 3.00,
            ],

            // Low Stock Items (for testing alerts)
            [
                'name' => 'Headlight Assembly - LED',
                'sku' => 'RET-HED-LED-011',
                'type' => 'retail',
                'stock_qty' => 3.00,
                'buy_price' => 280.00,
                'sell_price' => 450.00,
                'alert_limit' => 5.00,
            ],
            [
                'name' => 'Welding Wire - 0.035"',
                'sku' => 'MAT-WLD-035-016',
                'type' => 'material',
                'stock_qty' => 8.00,
                'buy_price' => 45.00,
                'sell_price' => 75.00,
                'alert_limit' => 10.00,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('Successfully seeded ' . count($products) . ' products!');
    }
}
