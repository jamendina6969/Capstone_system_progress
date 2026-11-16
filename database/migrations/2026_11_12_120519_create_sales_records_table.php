<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safely check if the table exists before creating it
        if (!Schema::hasTable('sales_records')) {
            Schema::create('sales_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('cascade');
                $table->date('record_date');
                $table->integer('quantity');
                $table->string('source')->default('pos'); // 'pos', 'imported'
                $table->timestamps();

                $table->index(['product_id', 'record_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_records');
    }
};