<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_item_materials', function (Blueprint $table) {
            $table->string('product_sku')->nullable()->after('product_id');
            $table->string('item_sku')->nullable()->after('product_sku');

            $table->index('product_sku');
            $table->index('item_sku');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_item_materials', function (Blueprint $table) {
            $table->dropIndex(['product_sku']);
            $table->dropIndex(['item_sku']);
            $table->dropColumn(['product_sku', 'item_sku']);
        });
    }
};