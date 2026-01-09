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
        if (!Schema::hasColumn('filters', 'user_id')) {
            Schema::table('filters', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('filters', 'user_id') && Schema::hasColumn('filters', 'filter_type')) {
            Schema::table('filters', function (Blueprint $table) {
                $table->unique(['user_id', 'filter_type'], 'filters_user_type_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('filters', 'user_id') && Schema::hasColumn('filters', 'filter_type')) {
            Schema::table('filters', function (Blueprint $table) {
                $table->dropUnique('filters_user_type_unique');
            });
        }

        if (Schema::hasColumn('filters', 'user_id')) {
            Schema::table('filters', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
