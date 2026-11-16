<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the table first just in case a broken version was left behind
        Schema::dropIfExists('demand_records');

        Schema::create('demand_records', function (Blueprint $table) {
            $table->id();
            
            // Manually define the column and give the foreign key a unique custom name
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id', 'fk_demand_records_product_id')
                  ->references('id')->on('products')->onDelete('cascade');
                  
            $table->date('record_date');
            $table->integer('quantity');
            $table->string('source')->default('sale'); 
            $table->timestamps();

            $table->index(['product_id', 'record_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demand_records');
    }
};