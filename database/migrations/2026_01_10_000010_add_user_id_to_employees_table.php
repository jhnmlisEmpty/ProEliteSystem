<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'user_id')) {
                $table->foreignId('user_id')
                    ->unique()
                    ->nullable()
                    ->after('branch_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            }
        });

        // Backfill employees for existing users with role "employee"
        DB::table('users')
            ->where('role', 'employee')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $existing = DB::table('employees')->where('user_id', $user->id)->first();
                    if ($existing) {
                        DB::table('employees')->where('id', $existing->id)->update([
                            'name' => $user->name,
                            'branch_id' => $user->branch_id,
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('employees')->insert([
                            'name' => $user->name,
                            'branch_id' => $user->branch_id,
                            'user_id' => $user->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropUnique(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
