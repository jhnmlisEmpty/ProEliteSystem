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
        Schema::create('vips', function (Blueprint $table) {
            $table->id();
            
            // Stepboard
            $table->integer('stepboard_pcs')->default(0);
            $table->integer('stepboard_amount')->default(0); // stored as integer (pesos)
            
            // Engine Bay
            $table->integer('engine_bay_pcs')->default(0);
            $table->integer('engine_bay_amount')->default(0); // stored as integer (pesos)
            
            // Console Box
            $table->integer('console_box_pcs')->default(0);
            $table->integer('console_box_amount')->default(0); // stored as integer (pesos)
            
            // Additional fields
            $table->text('description')->nullable();
            $table->string('photo')->nullable(); // path to photo file
            
            // Total amount
            $table->integer('total_amount')->default(0); // stored as integer (pesos)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vips');
    }
};
