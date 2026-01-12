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
            // Add discount fields if they don't exist
            if (!Schema::hasColumn('orders', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage')->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('orders', 'discounted_amount')) {
                $table->integer('discounted_amount')->default(0)->after('discount_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
            if (Schema::hasColumn('orders', 'discount_value')) {
                $table->dropColumn('discount_value');
            }
            if (Schema::hasColumn('orders', 'discounted_amount')) {
                $table->dropColumn('discounted_amount');
            }
        });
    }
};
