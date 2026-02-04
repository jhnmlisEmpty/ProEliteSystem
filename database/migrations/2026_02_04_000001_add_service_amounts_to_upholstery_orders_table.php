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
        Schema::table('upholstery_orders', function (Blueprint $table) {
            // Service amounts
            $table->integer('seat_cover_amount')->default(0)->after('services');
            $table->text('seat_cover_description')->nullable()->after('seat_cover_amount');
            
            $table->integer('ceiling_amount')->default(0)->after('seat_cover_description');
            $table->text('ceiling_description')->nullable()->after('ceiling_amount');
            
            $table->integer('sidings_amount')->default(0)->after('ceiling_description');
            $table->text('sidings_description')->nullable()->after('sidings_amount');
            
            $table->integer('rubber_mattings_amount')->default(0)->after('sidings_description');
            $table->text('rubber_mattings_description')->nullable()->after('rubber_mattings_amount');
            
            $table->integer('front_mattings_amount')->default(0)->after('rubber_mattings_description');
            $table->text('front_mattings_description')->nullable()->after('front_mattings_amount');
            
            $table->integer('headrest_amount')->default(0)->after('front_mattings_description');
            $table->text('headrest_description')->nullable()->after('headrest_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upholstery_orders', function (Blueprint $table) {
            $table->dropColumn([
                'seat_cover_amount',
                'seat_cover_description',
                'ceiling_amount',
                'ceiling_description',
                'sidings_amount',
                'sidings_description',
                'rubber_mattings_amount',
                'rubber_mattings_description',
                'front_mattings_amount',
                'front_mattings_description',
                'headrest_amount',
                'headrest_description',
            ]);
        });
    }
};
