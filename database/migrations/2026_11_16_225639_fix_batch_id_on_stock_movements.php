<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // batch_id is used to group movements. Return numbers like
        // 'RET-20261116-000001' (21 chars) don't fit char(36) cleanly,
        // so widen the column.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE stock_movements
                MODIFY COLUMN batch_id VARCHAR(255) NULL
            ");
        } else {
            \Illuminate\Support\Facades\Schema::table('stock_movements', function ($table) {
                $table->string('batch_id', 255)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE stock_movements
                MODIFY COLUMN batch_id CHAR(36) NULL
            ");
        } else {
            \Illuminate\Support\Facades\Schema::table('stock_movements', function ($table) {
                $table->char('batch_id', 36)->nullable()->change();
            });
        }
    }
};