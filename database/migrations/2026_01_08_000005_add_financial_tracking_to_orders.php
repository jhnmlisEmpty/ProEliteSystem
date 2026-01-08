<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing columns to services table and create new expense/assignment tables
     */
    public function up(): void
    {
        // Add client_price to services (cost to charge customer)
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'client_price')) {
                $table->integer('client_price')->default(0)->after('base_labor_cost');
            }
        });

        // Add financial tracking to orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'total_gross')) {
                $table->integer('total_gross')->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'total_cost')) {
                $table->integer('total_cost')->default(0)->after('total_gross');
            }
            if (!Schema::hasColumn('orders', 'net_income')) {
                $table->integer('net_income')->default(0)->after('total_cost');
            }
        });

        // Create order_expenses table
        if (!Schema::hasTable('order_expenses')) {
            Schema::create('order_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('description');
                $table->integer('my_cost')->default(0); // Cost to you
                $table->integer('charge_client')->default(0); // Charge to customer
                $table->boolean('is_billable')->default(false); // Whether to include in customer bill
                $table->timestamps();

                $table->index('order_id');
            });
        }

        // Create service_assignments table to track crew per service in order
        if (!Schema::hasTable('service_assignments')) {
            Schema::create('service_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('service_id')->constrained()->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['order_id', 'service_id']);
                $table->index('employee_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_assignments');
        Schema::dropIfExists('order_expenses');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'total_gross')) {
                $table->dropColumn('total_gross');
            }
            if (Schema::hasColumn('orders', 'total_cost')) {
                $table->dropColumn('total_cost');
            }
            if (Schema::hasColumn('orders', 'net_income')) {
                $table->dropColumn('net_income');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'client_price')) {
                $table->dropColumn('client_price');
            }
        });
    }
};
