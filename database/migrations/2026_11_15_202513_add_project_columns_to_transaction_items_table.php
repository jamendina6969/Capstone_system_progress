<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            // 1. Distinguishes between a regular 'product' and a 'project'
            if (!Schema::hasColumn('transaction_items', 'item_type')) {
                $table->string('item_type')->default('product')->after('transaction_id');
            }
            
            // 2. Stores the uploaded customer/company image
            if (!Schema::hasColumn('transaction_items', 'project_image')) {
                $table->string('project_image')->nullable()->after('item_type');
            }

            // 3. Stores the JSON snapshot of the materials, labor, markup, etc.
            if (!Schema::hasColumn('transaction_items', 'project_breakdown')) {
                $table->json('project_breakdown')->nullable()->after('project_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'project_image', 'project_breakdown']);
        });
    }
};