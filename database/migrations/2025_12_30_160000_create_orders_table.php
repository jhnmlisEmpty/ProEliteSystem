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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name'); // Denormalized for quick access
            $table->string('vehicle_type')->nullable();
            $table->string('plate_number')->nullable();
            $table->enum('type', ['product', 'service', 'both'])->default('product');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->integer('total_amount')->default(0); // Whole number (peso)
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
