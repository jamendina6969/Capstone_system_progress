<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'user_id',
        'creator_name', // <--- ADDED
        'expected_delivery_date',
        'status',
        'notes',
        'total_amount',
        'subtotal',
        'tax_amount',
        'freight_amount',
        'received_quantity',
        'discrepancy_amount',
        'grn_notes',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'freight_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discrepancy_amount' => 'decimal:2',
    ];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(PurchaseOrderItem::class); }

    public function getGrandTotalAttribute()
    {
        return (float) $this->subtotal + (float) $this->tax_amount + (float) $this->freight_amount;
    }
}