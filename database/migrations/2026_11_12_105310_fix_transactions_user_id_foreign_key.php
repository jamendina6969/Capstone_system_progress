<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Drop the strict foreign key constraint that is blocking deletion
            $table->dropForeign(['user_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            // 2. Make the column nullable
            $table->foreignId('user_id')->nullable()->change();
            
            // 3. Re-add the foreign key with nullOnDelete (so deleting a user sets this to NULL)
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        //
    }
};