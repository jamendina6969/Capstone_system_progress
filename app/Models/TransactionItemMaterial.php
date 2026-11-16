<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItemMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_item_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'subtotal', // <--- ADDED BACK
        'source',
    ];

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}