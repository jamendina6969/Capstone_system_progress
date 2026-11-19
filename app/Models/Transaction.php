<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges;

class Transaction extends Model
{
    use HasFactory, AuditsChanges;

    // Formal Transaction Type Constants (replaces magic numbers)
    public const TYPE_SALE = 0;
    public const TYPE_IMPORTED = 1;
    public const TYPE_REFUND = 2;

    protected $fillable = [
        'customer_return_id',   // ← NEW
        'user_id',
        'cashier_name',
        'transaction_id',
        'transaction_type',
        'total_amount',
        'amount_paid',
        'amount_change',
        'cart_discount_type',
        'cart_discount_value',
        'customer_name',
    ];

    protected $casts = [
        'total_amount'  => 'decimal:2',
        'amount_paid'   => 'decimal:2',
        'amount_change' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    // ← NEW
    public function customerReturn()
    {
        return $this->belongsTo(CustomerReturn::class);
    }

    // ← NEW
    public function scopeSales($query)
    {
        return $query->where('transaction_type', 0);
    }

    // ← NEW
    public function scopeRefunds($query)
    {
        return $query->where('transaction_type', 2);
    }

    // ← NEW (helper for blades)
    public function isRefund(): bool
    {
        return (int) $this->transaction_type === self::TYPE_REFUND;
    }

    public function getTransactionTypeNameAttribute(): string
    {
        return match ((int) $this->transaction_type) {
            self::TYPE_IMPORTED => 'Imported CSV',
            self::TYPE_REFUND => 'Customer Refund',
            default => 'POS Retail Sale',
        };
    }
}