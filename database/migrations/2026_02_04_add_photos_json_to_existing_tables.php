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
        // Add photos column to vips table
        Schema::table('vips', function (Blueprint $table) {
            $table->json('photos')->nullable()->after('photo');
        });

        // Add photos column to upholstery_orders table
        Schema::table('upholstery_orders', function (Blueprint $table) {
            $table->json('photos')->nullable()->after('photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vips', function (Blueprint $table) {
            $table->dropColumn('photos');
        });

        Schema::table('upholstery_orders', function (Blueprint $table) {
            $table->dropColumn('photos');
        });
    }
};
