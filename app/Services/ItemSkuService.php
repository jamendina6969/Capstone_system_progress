<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductItemSku;
use Illuminate\Support\Facades\DB;

class ItemSkuService
{
    private const MAX_BATCH_UNITS = 5000;

    /**
     * Generate sequential unit-level item_skus when stock enters Store or Warehouse.
     * Format: {PRODUCT_SKU}-0001, {PRODUCT_SKU}-0002, ...
     */
    public function generateUnits(
        Product $product,
        int $quantity,
        string $location = 'warehouse',
        ?int $purchaseOrderId = null
    ): array {
        $qty = min(self::MAX_BATCH_UNITS, max(0, $quantity));
        if ($qty <= 0) {
            return [];
        }

        $location = in_array($location, ['store', 'warehouse'], true) ? $location : 'warehouse';
        $baseSku = strtoupper(trim((string) ($product->product_sku ?: 'PRD-' . $product->id)));

        // Determine highest existing sequence number for this product
        $prefix = $baseSku . '-';
        $prefixLen = strlen($prefix);

        $maxSeq = (int) ProductItemSku::where('product_id', $product->id)
            ->where('item_sku', 'like', $prefix . '%')
            ->pluck('item_sku')
            ->map(function ($sku) use ($prefixLen) {
                $tail = substr((string) $sku, $prefixLen);
                return ctype_digit($tail) ? (int) $tail : 0;
            })
            ->max();

        $nextSeq = max($maxSeq, ProductItemSku::where('product_id', $product->id)->count()) + 1;
        $now = now();
        $rows = [];
        $generatedSkus = [];

        for ($i = 0; $i < $qty; $i++) {
            $sku = sprintf('%s-%04d', $baseSku, $nextSeq++);
            $generatedSkus[] = $sku;
            $rows[] = [
                'product_id'        => $product->id,
                'item_sku'          => $sku,
                'location'          => $location,
                'status'            => ProductItemSku::STATUS_IN_STOCK,
                'purchase_order_id' => $purchaseOrderId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('product_item_skus')->insertOrIgnore($chunk);
        }

        return $generatedSkus;
    }

    /**
     * Move N oldest in-stock unit SKUs (FIFO) between warehouse and store.
     */
    public function transferUnits(
        Product $product,
        int $quantity,
        string $fromLocation = 'warehouse',
        string $toLocation = 'store'
    ): int {
        $qty = max(0, $quantity);
        if ($qty <= 0 || $fromLocation === $toLocation) {
            return 0;
        }

        $ids = ProductItemSku::where('product_id', $product->id)
            ->where('location', $fromLocation)
            ->where('status', ProductItemSku::STATUS_IN_STOCK)
            ->orderBy('id')
            ->limit($qty)
            ->lockForUpdate()
            ->pluck('id')
            ->all();

        $moved = 0;
        if (!empty($ids)) {
            $moved = ProductItemSku::whereIn('id', $ids)->update([
                'location'   => $toLocation,
                'updated_at' => now(),
            ]);
        }

        // If legacy stock existed without unit rows, top up destination so traceability stays intact
        $missing = $qty - $moved;
        if ($missing > 0) {
            $this->generateUnits($product, $missing, $toLocation);
            $moved += $missing;
        }

        return $moved;
    }

    /**
     * Reconcile unit SKUs when an admin manually adjusts stock counts on Edit Product.
     */
    public function adjustLocationCount(Product $product, string $location, int $delta): void
    {
        if ($delta > 0) {
            $this->generateUnits($product, $delta, $location);
        } elseif ($delta < 0) {
            $removeCount = abs($delta);
            $ids = ProductItemSku::where('product_id', $product->id)
                ->where('location', $location)
                ->where('status', ProductItemSku::STATUS_IN_STOCK)
                ->orderByDesc('id')
                ->limit($removeCount)
                ->lockForUpdate()
                ->pluck('id')
                ->all();

            if (!empty($ids)) {
                ProductItemSku::whereIn('id', $ids)->delete();
            }
        }
    }

    /**
     * FIFO allocate N in-stock unit SKUs for a POS sale (online or offline sync).
     * Returns the list of assigned item_sku strings.
     */
    public function allocateForSale(
        Product $product,
        int $quantity,
        string $location,
        int $transactionId,
        ?int $transactionItemId = null
    ): array {
        $qty = max(1, $quantity);
        $location = in_array($location, ['store', 'warehouse'], true) ? $location : 'store';

        $units = ProductItemSku::where('product_id', $product->id)
            ->where('location', $location)
            ->where('status', ProductItemSku::STATUS_IN_STOCK)
            ->orderBy('id')
            ->limit($qty)
            ->lockForUpdate()
            ->get();

        // Top up gracefully if legacy stock lacked unit SKU records
        $shortfall = $qty - $units->count();
        if ($shortfall > 0) {
            $this->generateUnits($product, $shortfall, $location);
            $units = ProductItemSku::where('product_id', $product->id)
                ->where('location', $location)
                ->where('status', ProductItemSku::STATUS_IN_STOCK)
                ->orderBy('id')
                ->limit($qty)
                ->lockForUpdate()
                ->get();
        }

        $ids = $units->pluck('id')->all();
        if (!empty($ids)) {
            ProductItemSku::whereIn('id', $ids)->update([
                'status'              => ProductItemSku::STATUS_SOLD,
                'transaction_id'      => $transactionId,
                'transaction_item_id' => $transactionItemId,
                'updated_at'          => now(),
            ]);
        }

        return $units->pluck('item_sku')->all();
    }

    /**
     * Format an array of unit SKUs cleanly into a compact display string for TransactionItem.item_sku.
     */
    public function formatAssignedSkuLabel(array $skus, Product $product): string
    {
        if (empty($skus)) {
            return strtoupper((string) $product->product_sku) . '-' . strtoupper(substr(uniqid(), -5));
        }
        if (count($skus) === 1) {
            return $skus[0];
        }
        // Store first SKU if comma list would exceed 250 chars; otherwise comma-join all assigned SKUs
        $joined = implode(', ', $skus);
        return strlen($joined) <= 250 ? $joined : ($skus[0] . ' (+' . (count($skus) - 1) . ' more)');
    }

    /**
     * Process a customer return for sold unit SKUs on a transaction.
     * Good units return to Store (in_stock); defective units move to Warehouse holding (returned_defective)
     * while preserving purchase_order_id so they can be traced directly back to their supplier!
     */
    public function processCustomerReturn(
        int $productId,
        int $quantity,
        string $condition,
        int $transactionId,
        ?string $specificSku = null,
        ?int $transactionItemId = null
    ): array {
        $isGood = in_array(strtolower($condition), ['good', 'resellable', 'sellable'], true);
        $newStatus = $isGood ? ProductItemSku::STATUS_IN_STOCK : ProductItemSku::STATUS_RETURNED_DEFECTIVE;
        $newLocation = $isGood ? 'store' : 'warehouse';

        $query = ProductItemSku::where('product_id', $productId)
            ->where('status', ProductItemSku::STATUS_SOLD);

        if (!empty($specificSku)) {
            $exact = (clone $query)->where('item_sku', $specificSku)->lockForUpdate()->first();
            if ($exact) {
                $units = collect([$exact]);
                if ($quantity > 1) {
                    $extra = (clone $query)
                        ->where('transaction_id', $transactionId)
                        ->where('id', '!=', $exact->id)
                        ->orderBy('id')
                        ->limit($quantity - 1)
                        ->lockForUpdate()
                        ->get();
                    $units = $units->merge($extra);
                }
            } else {
                $units = (clone $query)
                    ->where('transaction_id', $transactionId)
                    ->when($transactionItemId, fn ($q) => $q->where('transaction_item_id', $transactionItemId))
                    ->orderBy('id')
                    ->limit($quantity)
                    ->lockForUpdate()
                    ->get();
            }
        } else {
            $units = $query
                ->where('transaction_id', $transactionId)
                ->when($transactionItemId, fn ($q) => $q->where('transaction_item_id', $transactionItemId))
                ->orderBy('id')
                ->limit($quantity)
                ->lockForUpdate()
                ->get();
        }

        $ids = $units->pluck('id')->all();
        if (!empty($ids)) {
            ProductItemSku::whereIn('id', $ids)->update([
                'status'              => $newStatus,
                'location'            => $newLocation,
                'transaction_id'      => $isGood ? null : $transactionId,
                'transaction_item_id' => $isGood ? null : $transactionItemId,
                'updated_at'          => now(),
            ]);
        }

        return $units->pluck('item_sku')->all();
    }

    /**
     * Mark units as returned_to_supplier when a Supplier Return is approved.
     */
    public function allocateForSupplierReturn(
        Product $product,
        int $quantity,
        int $supplierReturnId,
        string $source = 'warehouse'
    ): int {
        $qty = max(1, $quantity);

        if ($source === 'defective_returns') {
            $ids = ProductItemSku::where('product_id', $product->id)
                ->where('status', ProductItemSku::STATUS_RETURNED_DEFECTIVE)
                ->orderBy('id')
                ->limit($qty)
                ->lockForUpdate()
                ->pluck('id')
                ->all();
        } else {
            $loc = $source === 'store' ? 'store' : 'warehouse';
            $ids = ProductItemSku::where('product_id', $product->id)
                ->where('location', $loc)
                ->where('status', ProductItemSku::STATUS_IN_STOCK)
                ->orderBy('id')
                ->limit($qty)
                ->lockForUpdate()
                ->pluck('id')
                ->all();
        }

        if (empty($ids)) {
            return 0;
        }

        return ProductItemSku::whereIn('id', $ids)->update([
            'status'             => ProductItemSku::STATUS_RETURNED_TO_SUPPLIER,
            'supplier_return_id' => $supplierReturnId,
            'updated_at'         => now(),
        ]);
    }
}
