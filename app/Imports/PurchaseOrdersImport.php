<?php

namespace App\Imports;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;

class PurchaseOrdersImport implements ToCollection
{
    public function __construct(private string $onDuplicate = 'skip') {}

    public function collection(Collection $rows)
    {
        $headerRow = $rows->shift();
        if (!$headerRow) return;

        $headers = [];
        foreach ($headerRow as $col) {
            $cleanCol = preg_replace('/[^a-zA-Z0-9]/', '', str_replace("\xEF\xBB\xBF", '', (string) $col));
            $headers[] = strtolower($cleanCol);
        }

        $dataRows = [];
        foreach ($rows as $row) {
            $rowArray = $row->toArray();
            $rowArray = array_pad($rowArray, count($headers), null);
            $dataRows[] = array_combine($headers, array_slice($rowArray, 0, count($headers)));
        }

        $groupedRows = [];
        foreach ($dataRows as $data) {
            $supplierName = trim((string) ($data['suppliername'] ?? ''));
            $expectedDate = trim((string) ($data['expecteddeliverydate'] ?? ''));
            
            if ($supplierName === '') continue;

            // This key is what separates them into different POs
            $groupKey = $supplierName . '_' . $expectedDate;
            $groupedRows[$groupKey][] = $data;
        }

        DB::transaction(function () use ($groupedRows) {
            foreach ($groupedRows as $groupKey => $items) {
                $firstItem = $items[0];
                
                $supplierName = trim((string) ($firstItem['suppliername'] ?? ''));
                $expectedDateRaw = $firstItem['expecteddeliverydate'] ?? null;
                
                // Safe Date Parsing
                $expectedDate = null;
                if ($expectedDateRaw) {
                    try {
                        $expectedDate = Carbon::parse($expectedDateRaw)->toDateString();
                    } catch (\Exception $e) {
                        $expectedDate = null;
                    }
                }
                
                $notes = $firstItem['notes'] ?? null;
                
                $taxAmount = 0;
                $freightAmount = 0;
                foreach ($items as $item) {
                    if ($taxAmount == 0 && !empty($item['potaxamount']) && is_numeric($item['potaxamount'])) {
                        $taxAmount = (float) $item['potaxamount'];
                    }
                    if ($freightAmount == 0 && !empty($item['pofreightamount']) && is_numeric($item['pofreightamount'])) {
                        $freightAmount = (float) $item['pofreightamount'];
                    }
                }

                $supplier = Supplier::firstOrCreate(
                    ['name' => $supplierName],
                    ['status' => 'active']
                );

                $poNumber = 'PO-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                $po = PurchaseOrder::create([
                    'po_number'              => $poNumber,
                    'supplier_id'            => $supplier->id,
                    'status'                 => 'pending',
                    'expected_delivery_date' => $expectedDate,
                    'notes'                  => $notes,
                    'tax_amount'             => $taxAmount,
                    'freight_amount'         => $freightAmount,
                    'user_id'                => auth()->id(),
                    'creator_name'           => auth()->check() ? (auth()->user()->firstname . ' ' . auth()->user()->lastname) : 'System Import',
                    'total_amount'           => 0,
                    'subtotal'               => 0,
                ]);

                $subtotal = 0;

                foreach ($items as $item) {
                    $productName = trim((string) ($item['productname'] ?? ''));
                    $quantity    = (int)   ($item['quantity'] ?? $item['qty'] ?? 0);
                    $unitCost    = (float) ($item['unitcost'] ?? 0);
                    
                    $mainCategoryName = trim((string) ($item['maincategory'] ?? 'Uncategorized'));
                    $brandName        = trim((string) ($item['brand'] ?? ''));

                    if ($productName === '' || $quantity <= 0) continue;

                    $product = Product::where('product_name', $productName)->first();

                    if (!$product) {
                        if ($mainCategoryName === '') $mainCategoryName = 'Uncategorized';
                        
                        $mainCat = Category::firstOrCreate(
                            ['name' => $mainCategoryName, 'parent_id' => null],
                            ['type' => 'product', 'code' => strtoupper(substr(md5($mainCategoryName), 0, 6))]
                        );

                        $categoryId = $mainCat->id;
                        if ($brandName !== '') {
                            $brandCat = Category::firstOrCreate(
                                ['name' => $brandName, 'parent_id' => $mainCat->id],
                                ['type' => 'product', 'code' => strtoupper(substr(md5($mainCategoryName . $brandName), 0, 6))]
                            );
                            $categoryId = $brandCat->id;
                        }

                        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $productName), 0, 3));
                        if (strlen($prefix) < 3) $prefix = str_pad($prefix, 3, 'X');
                        do {
                            $sku = $prefix . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
                        } while (Product::where('product_sku', $sku)->exists());

                        $product = Product::create([
                            'product_sku' => $sku,
                            'product_name' => $productName,
                            'product_description' => 'Auto-created from PO Import on ' . now()->format('M d, Y'),
                            'purchase_cost' => $unitCost,
                            'selling_price' => $unitCost,
                            'store_quantity' => 0,
                            'warehouse_quantity' => 0,
                            'category_id' => $categoryId,
                        ]);
                    }

                    $itemSubtotal = $quantity * $unitCost;
                    $subtotal += $itemSubtotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id'        => $product->id,
                        'quantity'          => $quantity,
                        'unit_cost'         => $unitCost,
                        'subtotal'          => $itemSubtotal,
                    ]);
                }

                $po->subtotal = $subtotal;
                $po->total_amount = $subtotal + $taxAmount + $freightAmount;
                $po->save();
            }
        });
    }
}