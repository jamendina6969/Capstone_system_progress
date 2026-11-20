<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\AuditsChanges;

class Supplier extends Model
{
    use HasFactory, AuditsChanges;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'province',
        'city',
        'barangay',
        'postal_code',
        'street',
        'address',
        'status',
    ];

    // Automatically append computed metrics to JSON/Array outputs
    protected $appends = ['average_rating', 'full_address', 'discrepancy_count', 'delivery_accuracy'];

    public function supplierReturns()
    {
        return $this->hasMany(SupplierReturn::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([$this->street, $this->barangay, $this->city, $this->province, $this->postal_code]);
        return !empty($parts) ? implode(', ', $parts) : ($this->address ?? 'No address specified');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function ledgers()
    {
        return $this->hasMany(SupplierLedger::class);
    }

    public function ratings()
    {
        return $this->hasMany(SupplierRating::class);
    }

    public function getBalanceAttribute()
    {
        $purchases = $this->ledgers()
            ->where('transaction_type', 'purchase')
            ->where(function ($query) {
                $query->whereNull('purchase_order_id')
                    ->orWhereHas('purchaseOrder', function ($poQuery) {
                        $poQuery->where('status', 'received');
                    });
            })
            ->sum('amount');

        $legacyShortages = $this->ledgers()
            ->where('transaction_type', 'payment')
            ->whereNotNull('purchase_order_id')
            ->where('description', 'like', 'Discrepancy:%')
            ->whereHas('purchaseOrder', function ($query) {
                $query->where('status', 'received');
            })
            ->sum('amount');

        $payments = $this->ledgers()
            ->where('transaction_type', 'payment')
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', 'not like', 'Discrepancy:%');
            })
            ->sum(DB::raw('amount - COALESCE(change_amount, 0)'));

        $returnCredits = $this->ledgers()
            ->where('transaction_type', 'return_credit')
            ->sum('amount');

        return round(
            (float) $purchases
            - (float) $legacyShortages
            - (float) $payments
            - (float) $returnCredits,
            2
        );
    }

    public function getDiscrepancyCountAttribute(): int
    {
        return $this->purchaseOrders()
            ->whereIn('status', ['received', 'completed'])
            ->whereHas('items', function ($q) {
                $q->whereColumn('quantity', '>', 'received_quantity');
            })
            ->count();
    }

    public function getDeliveryAccuracyAttribute(): ?float
    {
        $totalReceivedPos = $this->purchaseOrders()
            ->whereIn('status', ['received', 'completed'])
            ->count();

        if ($totalReceivedPos === 0) {
            return null;
        }

        $cleanPos = max(0, $totalReceivedPos - $this->discrepancy_count);
        return round(($cleanPos / $totalReceivedPos) * 100, 1);
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('score') ?? 0;
    }
}