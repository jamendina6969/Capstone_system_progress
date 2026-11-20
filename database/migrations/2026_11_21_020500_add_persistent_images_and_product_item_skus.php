<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add DB-backed persistent product image columns (Wasmer-proof)
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'product_image_data')) {
                $table->mediumText('product_image_data')->nullable()->after('product_image');
            }
            if (!Schema::hasColumn('products', 'product_image_mime')) {
                $table->string('product_image_mime', 50)->nullable()->after('product_image_data');
            }
        });

        // 2. Add source_location to supplier_returns so returns know whether stock came from warehouse, store, or defective customer returns
        if (Schema::hasTable('supplier_returns') && !Schema::hasColumn('supplier_returns', 'source_location')) {
            Schema::table('supplier_returns', function (Blueprint $table) {
                $table->string('source_location', 30)->default('warehouse')->after('reason');
            });
        }

        // 3. Create serialized unit-level product_item_skus table
        if (!Schema::hasTable('product_item_skus')) {
            Schema::create('product_item_skus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('item_sku', 80)->unique();
                $table->string('location', 20)->default('warehouse'); // store | warehouse
                $table->string('status', 30)->default('in_stock');    // in_stock | sold | returned_defective | returned_to_supplier
                $table->unsignedBigInteger('purchase_order_id')->nullable()->index();
                $table->unsignedBigInteger('transaction_id')->nullable()->index();
                $table->unsignedBigInteger('transaction_item_id')->nullable()->index();
                $table->unsignedBigInteger('supplier_return_id')->nullable()->index();
                $table->timestamps();

                $table->index(['product_id', 'location', 'status'], 'product_item_skus_lookup_idx');
            });
        }

        // 4. Auto-backfill existing product stocks into product_item_skus (safe, chunked, idempotent)
        $products = DB::table('products')
            ->select('id', 'product_sku', 'store_quantity', 'warehouse_quantity')
            ->get();

        $now = now();
        foreach ($products as $product) {
            $alreadyHasSkus = DB::table('product_item_skus')
                ->where('product_id', $product->id)
                ->exists();

            if ($alreadyHasSkus) {
                continue;
            }

            $baseSku = strtoupper(trim((string) ($product->product_sku ?: 'PRD-' . $product->id)));
            $storeQty = min(5000, max(0, (int) $product->store_quantity));
            $whQty = min(5000, max(0, (int) $product->warehouse_quantity));

            $seq = 1;
            $rows = [];

            for ($i = 0; $i < $storeQty; $i++) {
                $rows[] = [
                    'product_id' => $product->id,
                    'item_sku'   => sprintf('%s-%04d', $baseSku, $seq++),
                    'location'   => 'store',
                    'status'     => 'in_stock',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            for ($i = 0; $i < $whQty; $i++) {
                $rows[] = [
                    'product_id' => $product->id,
                    'item_sku'   => sprintf('%s-%04d', $baseSku, $seq++),
                    'location'   => 'warehouse',
                    'status'     => 'in_stock',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('product_item_skus')->insertOrIgnore($chunk);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_item_skus');

        if (Schema::hasTable('supplier_returns') && Schema::hasColumn('supplier_returns', 'source_location')) {
            Schema::table('supplier_returns', function (Blueprint $table) {
                $table->dropColumn('source_location');
            });
        }

        Schema::table('products', function (Blueprint $table) {
            $cols = array_filter(
                ['product_image_data', 'product_image_mime'],
                fn ($c) => Schema::hasColumn('products', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
