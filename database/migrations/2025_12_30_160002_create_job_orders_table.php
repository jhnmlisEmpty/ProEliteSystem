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
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('job_orders');
    }
};
