<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges;

class SupplierLedger extends Model
{
    use HasFactory, AuditsChanges;

    protected $fillable = [
        'supplier_id', 
        'purchase_order_id', 
        'transaction_type', 
        'amount', 
        'change_amount',
        'description',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}