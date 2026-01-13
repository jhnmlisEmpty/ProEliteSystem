<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create upholstery_orders table
        Schema::create('upholstery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            
            // Vehicle Information
            $table->string('unit_type');
            $table->string('unit_year_model');
            $table->string('unit_color')->nullable();

            // Upholstery Services & Details
            $table->json('services'); // Selected services: ["seat_cover", "ceiling", ...]
            $table->longText('description');
            $table->string('photo_path')->nullable();
            $table->date('installation_date');

            // Financial Breakdown
            $table->integer('downpayment')->default(0);
            $table->integer('balance')->default(0);

            // Timestamps
            $table->timestamps();

            // Index for queries
            $table->index('installation_date');
        });

        // Add foreign key to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('upholstery_order_id')->nullable()->constrained('upholstery_orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeignIdFor('upholstery_orders');
            $table->dropColumn('upholstery_order_id');
        });

        Schema::dropIfExists('upholstery_orders');
    }
};
