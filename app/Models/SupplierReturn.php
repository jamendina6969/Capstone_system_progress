<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges; // <-- Add this

class SupplierReturn extends Model
{
    use HasFactory, AuditsChanges; // <-- Add this

    protected $fillable = [
        'return_number', 'supplier_id', 'purchase_order_id', 'user_id',
        'reason', 'source_location', 'notes', 'total_amount', 'status'
    ];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(SupplierReturnItem::class); }
}