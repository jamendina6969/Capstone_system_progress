<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_return_id',
        'transaction_item_id',
        'product_id',
        'quantity',
        'unit_refund_amount',
        'refund_amount', // <--- ADDED BACK
        'restock',
        'source',
    ];

    public function customerReturn()
    {
        return $this->belongsTo(CustomerReturn::class);
    }

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}