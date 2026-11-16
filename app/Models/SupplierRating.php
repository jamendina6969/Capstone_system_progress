<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges;

class SupplierRating extends Model
{
    use HasFactory, AuditsChanges;

    protected $fillable = ['supplier_id', 'purchase_order_id', 'user_id', 'score', 'comment'];

    public function supplier() 
    { 
        return $this->belongsTo(Supplier::class); 
    }
    
    public function purchaseOrder() 
    { 
        return $this->belongsTo(PurchaseOrder::class); 
    }
    
    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }
}