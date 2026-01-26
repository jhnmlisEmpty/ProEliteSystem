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
        Schema::create('upholstery_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('upholstery_id');
            $table->unsignedBigInteger('employee_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('upholstery_id')->references('id')->on('upholstery_orders')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upholstery_assignments');
    }
};
