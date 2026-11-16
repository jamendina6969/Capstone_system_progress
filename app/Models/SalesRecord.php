<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges;

class SalesRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'transaction_id',
        'record_date',
        'quantity',
        'source', // 'pos', 'imported'
    ];

    protected $casts = [
        'record_date' => 'date',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}