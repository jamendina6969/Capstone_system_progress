<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->integer('received_quantity')->nullable()->after('total_amount');
            $table->decimal('discrepancy_amount', 12, 2)->default(0)->after('received_quantity');
            $table->text('grn_notes')->nullable()->after('discrepancy_amount');
            $table->timestamp('received_at')->nullable()->after('grn_notes');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['received_quantity', 'discrepancy_amount', 'grn_notes', 'received_at']);
        });
    }
};