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
        Schema::table('vips', function (Blueprint $table) {
            // Add unit price columns for existing components
            $table->integer('stepboard_unit_price')->default(0)->after('stepboard_pcs');
            $table->integer('engine_bay_unit_price')->default(0)->after('engine_bay_pcs');
            $table->integer('console_box_unit_price')->default(0)->after('console_box_pcs');
            
            // Add Thai Ceiling component
            $table->integer('thai_ceiling_pcs')->default(0)->after('console_box_amount');
            $table->integer('thai_ceiling_unit_price')->default(0)->after('thai_ceiling_pcs');
            $table->integer('thai_ceiling_amount')->default(0)->after('thai_ceiling_unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vips', function (Blueprint $table) {
            $table->dropColumn([
                'stepboard_unit_price',
                'engine_bay_unit_price',
                'console_box_unit_price',
                'thai_ceiling_pcs',
                'thai_ceiling_unit_price',
                'thai_ceiling_amount',
            ]);
        });
    }
};
