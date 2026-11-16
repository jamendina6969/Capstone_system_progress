<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\CustomerReturn;
use App\Models\CustomerReturnItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\SalesRecord;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerReturnController extends Controller
{
    public function store(Request $request, Transaction $transaction)
    {
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.qty'       => 'required|integer|min:0',
            'items.*.whole_project' => 'sometimes|boolean',
            'items.*.materials' => 'sometimes|array',
            'items.*.restock'   => 'sometimes|boolean',
            'reason'            => 'required|string|max:255',
            'notes'             => 'nullable|string|max:1000',
            'refund_method'     => 'required|in:cash,original,store_credit',
            'client_request_id' => 'nullable|uuid',
        ]);

        if (! in_array((int) $transaction->transaction_type, [0, 1], true)) {
            return back()->with('error', 'Only sale transactions can be returned.');
        }

        if ($request->client_request_id) {
            $existing = CustomerReturn::where('client_request_id', $request->client_request_id)->first();
            if ($existing) {
                return back()->with('success', "Return already processed: {$existing->return_number}");
            }
        }

        try {
            $return = DB::transaction(function () use ($request, $transaction) {
                $user = Auth::user();

                $transaction = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
                $itemsById   = $transaction->transactionItems()->get()->keyBy('id');

                $originalTotal = (float) $transaction->total_amount;
                $sumSubtotal   = (float) $itemsById->sum('subtotal');
                $ratio         = $sumSubtotal > 0 ? $originalTotal / $sumSubtotal : 1.0;

                $lines = [];
                $totalRefund = 0.0;

                foreach ($request->items as $itemId => $data) {
                    $item = $itemsById->get((int) $itemId);
                    if (! $item) continue;

                    if ($item->item_type === 'project') {
                        $isWhole = (bool) ($data['whole_project'] ?? false);
                        $restock = (bool) ($data['restock'] ?? true);

                        if ($isWhole) {
                            $alreadyReturned = CustomerReturnItem::where('transaction_item_id', $item->id)
                                ->whereNull('product_id')
                                ->whereHas('customerReturn', fn ($q) => $q->where('status', 'completed'))
                                ->exists();
                            
                            if ($alreadyReturned) {
                                throw new \DomainException("The entire project has already been returned.");
                            }

                            $lines[] = [
                                'item' => $item,
                                'type' => 'whole_project',
                                'refund_amount' => (float) $item->subtotal,
                                'restock' => $restock,
                            ];
                            $totalRefund += (float) $item->subtotal;
                        } else {
                            $materialsReq = $data['materials'] ?? [];
                            $validMaterials = [];
                            $materialRefundTotal = 0.0;

                            $item->loadMissing('materials');
                            
                            foreach ($item->materials as $index => $mat) {
                                $reqQty = (int) ($materialsReq[$index] ?? 0);
                                if ($reqQty <= 0) continue;

                                $alreadyReturned = CustomerReturnItem::where('transaction_item_id', $item->id)
                                    ->where('product_id', $mat->product_id)
                                    ->whereHas('customerReturn', fn ($q) => $q->where('status', 'completed'))
                                    ->sum('quantity');

                                $max = (int) $mat->quantity - $alreadyReturned;
                                if ($reqQty > $max) {
                                    throw new \DomainException("Cannot return {$reqQty} of {$mat->product_name}. Max allowed: {$max}.");
                                }

                                $lineRefund = round($reqQty * (float) $mat->price, 2);
                                $materialRefundTotal += $lineRefund;

                                $validMaterials[] = [
                                    'product_id' => $mat->product_id,
                                    'qty' => $reqQty,
                                    'refund_amount' => $lineRefund,
                                    'source' => $mat->source ?? 'store',
                                ];
                            }

                            if (!empty($validMaterials)) {
                                $lines[] = [
                                    'item' => $item,
                                    'type' => 'partial_project',
                                    'materials' => $validMaterials,
                                    'refund_amount' => $materialRefundTotal,
                                    'restock' => $restock,
                                ];
                                $totalRefund += $materialRefundTotal;
                            }
                        }
                    } else {
                        $qty = (int) ($data['qty'] ?? 0);
                        if ($qty <= 0) continue;

                        $alreadyReturned = (int) CustomerReturnItem::where('transaction_item_id', $item->id)
                            ->whereHas('customerReturn', fn ($q) => $q->where('status', 'completed'))
                            ->sum('quantity');

                        $max = (int) $item->quantity - $alreadyReturned;
                        if ($qty > $max) {
                            throw new \DomainException("Cannot return {$qty} of {$item->product_name}. Max allowed: {$max}.");
                        }

                        $unitPaid  = (float) $item->subtotal / max((int) $item->quantity, 1);
                        $lineTotal = round($unitPaid * $qty * $ratio, 2);

                        $lines[] = [
                            'item' => $item,
                            'type' => 'product',
                            'qty' => $qty,
                            'refund_amount' => $lineTotal,
                            'restock' => (bool) ($data['restock'] ?? true),
                        ];
                        $totalRefund += $lineTotal;
                    }
                }

                if (empty($lines)) {
                    throw new \DomainException('No items selected for return.');
                }
                $totalRefund = round($totalRefund, 2);

                $return = CustomerReturn::create([
                    'transaction_id'    => $transaction->id,
                    'user_id'           => $user->id,
                    'return_number'     => 'TMP-' . Str::uuid(),
                    'refund_amount'     => $totalRefund,
                    'refund_method'     => $request->refund_method,
                    'reason'            => $request->reason,
                    'notes'             => $request->notes,
                    'status'            => 'completed',
                    'client_request_id' => $request->client_request_id,
                ]);

                $return->update([
                    'return_number' => 'RET-' . now()->format('Ymd') . '-' . str_pad((string) $return->id, 6, '0', STR_PAD_LEFT),
                ]);

                $refundTx = Transaction::create([
                    'customer_return_id'  => $return->id,
                    'user_id'             => $user->id,
                    'cashier_name'        => $user->full_name,
                    'transaction_id'      => 'REF-' . $return->return_number,
                    'transaction_type'    => 2,
                    'total_amount'        => -$totalRefund,
                    'amount_paid'         => -$totalRefund,
                    'amount_change'       => 0,
                    'cart_discount_type'  => 'none',
                    'cart_discount_value' => 0,
                    'customer_name'       => 'Refund for ' . $transaction->transaction_id,
                ]);

                $return->update(['refund_transaction_id' => $refundTx->id]);

                $restockValue = 0.0;

                foreach ($lines as $line) {
                    $item = $line['item'];
                    
                    if ($line['type'] === 'whole_project') {
                        CustomerReturnItem::create([
                            'customer_return_id' => $return->id,
                            'transaction_item_id'=> $item->id,
                            'product_id'         => null,
                            'quantity'           => 1,
                            'unit_refund_amount' => $line['refund_amount'],
                            'refund_amount'      => $line['refund_amount'],
                            'restock'            => $line['restock'],
                            'source'             => null,
                        ]);

                        if ($line['restock']) {
                            $item->loadMissing('materials');
                            foreach ($item->materials as $mat) {
                                $restockValue += $this->returnToStock(
                                    (int) $mat->product_id, (int) $mat->quantity, $mat->source ?? 'store', $return, $refundTx, $user
                                );
                            }
                        }
                    } elseif ($line['type'] === 'partial_project') {
                        foreach ($line['materials'] as $mat) {
                            CustomerReturnItem::create([
                                'customer_return_id' => $return->id,
                                'transaction_item_id'=> $item->id,
                                'product_id'         => $mat['product_id'],
                                'quantity'           => $mat['qty'],
                                'unit_refund_amount' => round($mat['refund_amount'] / $mat['qty'], 2),
                                'refund_amount'      => $mat['refund_amount'],
                                'restock'            => $line['restock'],
                                'source'             => $mat['source'],
                            ]);

                            if ($line['restock']) {
                                $restockValue += $this->returnToStock(
                                    (int) $mat['product_id'], (int) $mat['qty'], $mat['source'], $return, $refundTx, $user
                                );
                            }
                        }
                    } else {
                        CustomerReturnItem::create([
                            'customer_return_id' => $return->id,
                            'transaction_item_id'=> $item->id,
                            'product_id'         => $item->product_id,
                            'quantity'           => $line['qty'],
                            'unit_refund_amount' => round($line['refund_amount'] / $line['qty'], 2),
                            'refund_amount'      => $line['refund_amount'],
                            'restock'            => $line['restock'],
                            'source'             => 'store',
                        ]);

                        if ($line['restock']) {
                            $restockValue += $this->returnToStock(
                                $item->product_id, $line['qty'], 'store', $return, $refundTx, $user
                            );
                        }
                    }
                }

                $return->update(['restock_value' => round($restockValue, 2)]);

                return $return;
            });

            return back()->with('success', "Refund {$return->return_number} processed successfully.");

        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'client_request_id')) {
                return back()->with('success', 'Return already processed.');
            }
            Log::error('Return DB error', ['err' => $e->getMessage()]);
            return back()->with('error', 'Refund failed. No changes were made.')->withInput();
        } catch (\Throwable $e) {
            Log::error('Return failed', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Refund failed. No changes were made.')->withInput();
        }
    }

    public function items(Transaction $transaction)
    {
        if (! in_array((int) $transaction->transaction_type, [0, 1], true)) {
            return response()->json([]);
        }

        $rows = $transaction->transactionItems()
            ->with(['materials.product'])
            ->withSum(['customerReturnItems as returned_qty' => function ($q) {
                $q->whereHas('customerReturn', fn ($r) => $r->where('status', 'completed'));
            }], 'quantity')
            ->get()
            ->map(function ($i) {
                $wholeProjectReturned = false;
                $materialsData = [];

                if ($i->item_type === 'project') {
                    $wholeProjectReturned = CustomerReturnItem::where('transaction_item_id', $i->id)
                        ->whereNull('product_id')
                        ->whereHas('customerReturn', fn ($q) => $q->where('status', 'completed'))
                        ->exists();

                    foreach ($i->materials as $mat) {
                        $returned = CustomerReturnItem::where('transaction_item_id', $i->id)
                            ->where('product_id', $mat->product_id)
                            ->whereHas('customerReturn', fn ($q) => $q->where('status', 'completed'))
                            ->sum('quantity');
                        
                        $materialsData[] = [
                            'product_id' => $mat->product_id,
                            'name'       => $mat->product_name,
                            'sku'        => $mat->product ? $mat->product->product_sku : '',
                            'price'      => (float) $mat->price,
                            'qty'        => (int) $mat->quantity,
                            'returned'   => (int) $returned,
                            'max'        => max(0, (int) $mat->quantity - (int) $returned),
                            'source'     => $mat->source,
                        ];
                    }
                }

                return [
                    'id'                => $i->id,
                    'name'              => $i->product_name,
                    'item_type'         => $i->item_type ?? 'product', 
                    'qty'               => (int) $i->quantity,
                    'returned'          => (int) ($i->returned_qty ?? 0),
                    'max'               => max(0, (int) $i->quantity - (int) ($i->returned_qty ?? 0)),
                    'unit'              => round((float) $i->subtotal / max((int) $i->quantity, 1), 2),
                    'materials'         => $materialsData,
                    'whole_project_returned' => $wholeProjectReturned,
                    'product_sku'       => $i->product_sku, 
                    'item_sku'          => $i->item_sku,    
                ];
            });

        return response()->json($rows);
    }

    public function void(Request $request, CustomerReturn $customerReturn)
    {
        $request->validate(['void_reason' => 'required|string|max:255']);

        if ($customerReturn->status === 'voided') {
            return back()->with('error', 'This return has already been voided.');
        }

        DB::transaction(function () use ($customerReturn, $request) {
            $user = Auth::user();
            $customerReturn = CustomerReturn::whereKey($customerReturn->id)->lockForUpdate()->firstOrFail();

            foreach ($customerReturn->items as $line) {
                if (! $line->restock) continue;

                $source = $line->source ?? 'store';
                $col    = $source === 'warehouse' ? 'warehouse_quantity' : 'store_quantity';

                $product = Product::whereKey($line->product_id)->lockForUpdate()->first();
                if (! $product) continue;

                $before = (int) $product->{$col};
                $product->decrement($col, $line->quantity);

                StockMovement::create([
                    'batch_id'        => $customerReturn->return_number . '-VOID',
                    'product_id'      => $product->id,
                    'type'            => 'customer_return_void',
                    'quantity'        => -$line->quantity,
                    'quantity_before' => $before,
                    'quantity_after'  => $before - $line->quantity,
                    'status'          => 'approved',
                    'delivery_status' => 'delivered', // <--- FIXED: Added for the Model update
                    'requested_by'    => $user->id,
                    'requester_name'  => $user->full_name,
                    'approved_by'     => $user->id,
                    'approver_name'   => $user->full_name,
                    'note'            => "Void of return {$customerReturn->return_number}",
                ]);

                SalesRecord::create([
                    'product_id'     => $product->id,
                    'transaction_id' => $customerReturn->refund_transaction_id,
                    'record_date'    => now()->toDateString(),
                    'quantity'       => $line->quantity,
                    'source'         => 'pos',
                ]);
            }

            if ($customerReturn->refundTransaction) {
                $customerReturn->refundTransaction->update([
                    'total_amount' => 0,
                    'amount_paid'  => 0,
                ]);
            }

            $customerReturn->update([
                'status'    => 'voided',
                'voided_at' => now(),
                'voided_by' => $user->id,
                'notes'     => trim(($customerReturn->notes ?? '') . "\nVOID: " . $request->void_reason),
            ]);
        });

        return back()->with('success', "Return {$customerReturn->return_number} voided.");
    }

    private function returnToStock(
        int $productId,
        int $qty,
        string $source,
        CustomerReturn $return,
        Transaction $refundTx,
        $user
    ): float {
        $product = Product::whereKey($productId)->lockForUpdate()->first();
        if (! $product) return 0.0;

        $col    = $source === 'warehouse' ? 'warehouse_quantity' : 'store_quantity';
        $before = (int) $product->{$col};
        $product->increment($col, $qty);

        StockMovement::create([
            'batch_id'        => $return->return_number,
            'product_id'      => $product->id,
            'type'            => 'customer_return',
            'quantity'        => $qty,
            'quantity_before' => $before,
            'quantity_after'  => $before + $qty,
            'status'          => 'approved',
            'delivery_status' => 'delivered', // <--- FIXED: Added for the Model update
            'requested_by'    => $user->id,
            'requester_name'  => $user->full_name,
            'approved_by'     => $user->id,
            'approver_name'   => $user->full_name,
            'note'            => "Customer return {$return->return_number} → {$source}",
        ]);

        SalesRecord::create([
            'product_id'     => $product->id,
            'transaction_id' => $refundTx->id,
            'record_date'    => now()->toDateString(),
            'quantity'       => -$qty,
            'source'         => 'pos',
        ]);

        $unit = (float) ($product->purchase_cost ?? 0);
        return $unit * $qty;
    }
}