<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Switch method from ENUM to VARCHAR to allow new payment methods without schema changes
        Schema::table('payments', function (Blueprint $table) {
            $table->string('method', 50)->default('cash')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original ENUM definition
        DB::statement("ALTER TABLE payments MODIFY method ENUM('cash','card','bank_transfer','check') DEFAULT 'cash'");
    }
};
