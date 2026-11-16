<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('refund_transaction_id')->nullable()
                  ->constrained('transactions')->nullOnDelete();

            $table->string('return_number')->unique();
            $table->decimal('refund_amount', 15, 2);
            $table->decimal('restock_value', 15, 2)->default(0);
            $table->string('refund_method')->default('cash');
            $table->string('reason');
            $table->text('notes')->nullable();

            $table->string('status')->default('completed');
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();

            $table->uuid('client_request_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['transaction_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_returns');
    }
};