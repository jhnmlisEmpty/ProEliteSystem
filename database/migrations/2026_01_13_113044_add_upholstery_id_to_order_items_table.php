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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('upholstery_id')->nullable()->after('service_id')->constrained('upholstery_orders')->nullOnDelete();
            $table->index('upholstery_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['upholstery_id']);
            $table->dropIndex(['upholstery_id']);
            $table->dropColumn('upholstery_id');
        });
    }
};
