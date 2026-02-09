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
        Schema::table('orders', function (Blueprint $table) {
            // Change enum to include 'for_installation' status
            $table->enum('status', ['pending', 'in_progress', 'for_installation', 'completed', 'cancelled'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert enum back to original (remove 'for_installation')
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->change();
        });
    }
};
