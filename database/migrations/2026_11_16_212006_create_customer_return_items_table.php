<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_return_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->restrictOnDelete();

            $table->integer('quantity');
            $table->decimal('unit_refund_amount', 15, 2);
            $table->decimal('refund_amount', 15, 2);

            $table->boolean('restock')->default(true);
            $table->string('source')->nullable();

            $table->timestamps();

            $table->index('transaction_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_return_items');
    }
};