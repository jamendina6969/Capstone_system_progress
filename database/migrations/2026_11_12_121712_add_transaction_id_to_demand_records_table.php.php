<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demand_records', function (Blueprint $table) {
            // Add the column first
            $table->unsignedBigInteger('transaction_id')->nullable()->after('product_id');
            
            // Manually define the foreign key with a unique custom name
            $table->foreign('transaction_id', 'fk_demand_records_transaction_id')
                  ->references('id')->on('transactions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('demand_records', function (Blueprint $table) {
            $table->dropForeign('fk_demand_records_transaction_id');
            $table->dropColumn('transaction_id');
        });
    }
};