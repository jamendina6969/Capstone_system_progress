<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. AUDIT LOGS
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                // Check if the column exists before modifying it
                if (Schema::hasColumn('audit_logs', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->change();
                }
                // Add snapshot columns so we always have the name
                if (!Schema::hasColumn('audit_logs', 'actor_name')) {
                    $table->string('actor_name')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('audit_logs', 'actor_role')) {
                    $table->string('actor_role')->nullable()->after('actor_name');
                }
            });
        }

        // 2. STOCK MOVEMENTS
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('requested_by')->nullable()->change();
            $table->foreignId('approved_by')->nullable()->change();

            if (!Schema::hasColumn('stock_movements', 'requester_name')) {
                $table->string('requester_name')->nullable()->after('requested_by');
            }
            if (!Schema::hasColumn('stock_movements', 'approver_name')) {
                $table->string('approver_name')->nullable()->after('approved_by');
            }
        });

        // 3. TRANSACTIONS (POS Sales)
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                // Assuming it uses user_id or cashier_id for the POS user
                // You might need to change 'user_id' to 'cashier_id' depending on your column name
                if (Schema::hasColumn('transactions', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->change();
                    if (!Schema::hasColumn('transactions', 'cashier_name')) {
                        $table->string('cashier_name')->nullable()->after('user_id');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['actor_name', 'actor_role']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['requester_name', 'approver_name']);
        });

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'cashier_name')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('cashier_name');
            });
        }
    }
};