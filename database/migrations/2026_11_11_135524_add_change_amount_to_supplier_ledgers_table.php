<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->decimal('change_amount', 12, 2)->nullable()->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->dropColumn('change_amount');
        });
    }
};