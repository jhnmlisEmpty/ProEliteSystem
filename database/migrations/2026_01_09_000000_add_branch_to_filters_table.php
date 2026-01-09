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
        Schema::table('filters', function (Blueprint $table) {
            if (!Schema::hasColumn('filters', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('filter_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filters', function (Blueprint $table) {
            if (Schema::hasColumn('filters', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }
        });
    }
};
