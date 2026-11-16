<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('freight_amount', 12, 2)->default(0)->after('tax_amount');
        });

        // Backfill existing data
        \DB::table('purchase_orders')->update([
            'subtotal' => \DB::raw('total_amount'),
            'tax_amount' => 0,
            'freight_amount' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount', 'freight_amount']);
        });
    }
};