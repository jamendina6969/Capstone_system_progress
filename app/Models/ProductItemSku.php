<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductItemSku extends Model
{
    use HasFactory;

    public const STATUS_IN_STOCK = 'in_stock';
    public const STATUS_SOLD = 'sold';
    public const STATUS_RETURNED_DEFECTIVE = 'returned_defective';
    public const STATUS_RETURNED_TO_SUPPLIER = 'returned_to_supplier';

    protected $fillable = [
        'product_id',
        'item_sku',
        'location',
        'status',
        'purchase_order_id',
        'transaction_id',
        'transaction_item_id',
        'supplier_return_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function supplierReturn()
    {
        return $this->belongsTo(SupplierReturn::class);
    }
}
