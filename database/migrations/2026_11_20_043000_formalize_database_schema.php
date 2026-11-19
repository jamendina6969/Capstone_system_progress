<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Standardize categories.type to a flexible varchar(50) instead of single-value enum
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'type')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `categories` MODIFY `type` VARCHAR(50) NOT NULL DEFAULT 'product'");
            }
        }

        // 2. Standardize stock_movements status values to lowercase consistency
        if (Schema::hasTable('stock_movements')) {
            DB::statement("UPDATE `stock_movements` SET `status` = LOWER(`status`) WHERE `status` IS NOT NULL");
            if (Schema::hasColumn('stock_movements', 'delivery_status')) {
                DB::statement("UPDATE `stock_movements` SET `delivery_status` = LOWER(`delivery_status`) WHERE `delivery_status` IS NOT NULL");
            }
        }

        // 3. Ensure demand_records has a unique compound index on [product_id, record_date, source]
        if (Schema::hasTable('demand_records') && Schema::hasColumn('demand_records', 'source')) {
            // Check if index already exists
            $indexExists = false;
            if (DB::getDriverName() === 'mysql') {
                $indexes = DB::select("SHOW INDEX FROM `demand_records` WHERE Key_name = 'demand_records_product_date_source_unique'");
                $indexExists = !empty($indexes);
            }

            if (!$indexExists) {
                Schema::table('demand_records', function (Blueprint $table) {
                    $table->unique(['product_id', 'record_date', 'source'], 'demand_records_product_date_source_unique');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('demand_records')) {
            Schema::table('demand_records', function (Blueprint $table) {
                $table->dropUnique('demand_records_product_date_source_unique');
            });
        }
    }
};
