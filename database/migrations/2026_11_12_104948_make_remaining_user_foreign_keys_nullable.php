<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Helper function to safely update foreign keys
        $updateForeignKey = function ($tableName, $columnName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, $columnName)) {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                        // Drop the old strict foreign key if it exists
                        $table->dropForeign([$columnName]);
                    });
                } catch (\Exception $e) {
                    // Ignore if the foreign key doesn't exist
                }

                Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                    // Make it nullable and re-add with nullOnDelete()
                    $table->foreignId($columnName)->nullable()->change();
                    $table->foreign($columnName)->references('id')->on('users')->nullOnDelete();
                });
            }
        };

        // 1. Purchase Orders (Created by)
        $updateForeignKey('purchase_orders', 'user_id');

        // 2. Purchase Requests (Requested by)
        $updateForeignKey('purchase_requests', 'user_id');

        // 3. Supplier Returns (Processed by)
        $updateForeignKey('supplier_returns', 'user_id');

        // 4. Supplier Ratings (Rated by)
        $updateForeignKey('supplier_ratings', 'user_id');

        // 5. EOQ Settings (Updated by)
        $updateForeignKey('eoq_settings', 'updated_by');

        // 6. Forecast Settings (Updated by)
        $updateForeignKey('forecast_settings', 'updated_by');

        // 7. Product Ordering Costs (Set by)
        $updateForeignKey('product_ordering_costs', 'set_by');
    }

    public function down(): void
    {
        // No need to reverse this, it's safe to leave them nullable
    }
};