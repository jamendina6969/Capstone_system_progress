<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'total_quantity')) {
                $table->integer('total_quantity')->default(0)->after('warehouse_quantity');
            }

            $table->index('total_quantity');
            $table->index('store_quantity');
            $table->index('warehouse_quantity');
            $table->index('reorder_point');
            $table->index(['category_id', 'product_name']);
        });

        // Backfill total_quantity for all existing records
        DB::statement('UPDATE products SET total_quantity = COALESCE(store_quantity, 0) + COALESCE(warehouse_quantity, 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['total_quantity']);
            $table->dropIndex(['store_quantity']);
            $table->dropIndex(['warehouse_quantity']);
            $table->dropIndex(['reorder_point']);
            $table->dropIndex(['category_id', 'product_name']);

            if (Schema::hasColumn('products', 'total_quantity')) {
                $table->dropColumn('total_quantity');
            }
        });
    }
};
