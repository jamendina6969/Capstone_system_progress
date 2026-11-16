<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges;

class PurchaseRequest extends Model
{
    use HasFactory, AuditsChanges;

    protected $fillable = [
        'pr_number', 
        'user_id', 
        'requester_name', // <--- ADDED
        'supplier_id', 
        'status',
        'reason', 
        'manager_notes', 
        'approved_by', 
        'approved_at'
    ];

    protected $casts = ['approved_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function items() { return $this->hasMany(PurchaseRequestItem::class); }
}