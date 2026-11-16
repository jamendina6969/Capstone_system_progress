<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eoq_settings', function (Blueprint $table) {
            // Drop old mismatched column if it exists
            if (Schema::hasColumn('eoq_settings', 'holding_cost_per_liter')) {
                $table->dropColumn('holding_cost_per_liter');
            }
            
            // Add required columns if they don't exist
            if (!Schema::hasColumn('eoq_settings', 'ordering_cost_percentage')) {
                $table->decimal('ordering_cost_percentage', 5, 2)->default(10.00)->after('default_ordering_cost');
            }
            if (!Schema::hasColumn('eoq_settings', 'holding_cost_per_cbm')) {
                $table->decimal('holding_cost_per_cbm', 10, 2)->default(0.00)->after('holding_cost_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eoq_settings', function (Blueprint $table) {
            $table->dropColumn(['ordering_cost_percentage', 'holding_cost_per_cbm']);
            $table->decimal('holding_cost_per_liter', 10, 2)->default(0.00);
        });
    }
};