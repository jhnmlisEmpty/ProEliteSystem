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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->integer('amount')->default(0); // Whole number (peso)
            $table->enum('method', ['cash', 'card', 'bank_transfer', 'check'])->default('cash');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
