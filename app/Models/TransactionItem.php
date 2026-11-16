<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges;

class TransactionItem extends Model
{
    use HasFactory, AuditsChanges;

    protected $fillable = [
        'transaction_id',
        'item_type',           
        'project_image',       
        'project_breakdown',   
        'product_id',
        'product_sku',
        'item_sku',
        'product_name',
        'main_category_name',  
        'brand_category_name', 
        'selling_price',
        'quantity',
        'subtotal',
        'item_discount_type',
        'item_discount_value',
        'item_discount_scope',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'item_discount_value' => 'decimal:2',
        'project_breakdown' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customerReturnItems()
    {
        return $this->hasMany(CustomerReturnItem::class, 'transaction_item_id');
    }

    // <--- FIXED: Added materials relationship for Project Receipts
    public function materials()
    {
        return $this->hasMany(TransactionItemMaterial::class);
    }
}