<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges;

class CustomerReturn extends Model
{
    use HasFactory, AuditsChanges;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'refund_transaction_id',
        'return_number',
        'refund_amount',
        'restock_value',
        'refund_method',
        'reason',
        'notes',
        'status',
        'voided_at',
        'voided_by',
        'client_request_id',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'restock_value' => 'decimal:2',
        'voided_at'     => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function refundTransaction()
    {
        return $this->belongsTo(Transaction::class, 'refund_transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function items()
    {
        return $this->hasMany(CustomerReturnItem::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}