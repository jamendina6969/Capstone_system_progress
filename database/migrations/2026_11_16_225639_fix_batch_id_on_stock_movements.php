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
        DB::statement("
            ALTER TABLE stock_movements
            MODIFY COLUMN batch_id VARCHAR(255) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE stock_movements
            MODIFY COLUMN batch_id CHAR(36) NULL
        ");
    }
};