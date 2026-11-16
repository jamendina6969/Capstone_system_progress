<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_requests')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_requests', 'requester_name')) {
                    $table->string('requester_name')->nullable()->after('user_id');
                }
            });
        }

        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_orders', 'creator_name')) {
                    $table->string('creator_name')->nullable()->after('user_id');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};