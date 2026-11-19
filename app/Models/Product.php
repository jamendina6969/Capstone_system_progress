<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges;

class Product extends Model
{
    use HasFactory, AuditsChanges;

    protected $fillable = [
        'product_sku',
        'category_id',     
        'product_name',
        'product_description',
        'purchase_cost',
        'selling_price',
        'reorder_point',
        'approx_volume',
        'storage_volume_cbm',
        'product_image', 
        'store_quantity',
        'warehouse_quantity',
        'total_quantity',
        // <--- ADDED to support EoqService
    ];

    protected static function booted()
    {
        static::saving(function ($product) {
            $product->total_quantity = (int) ($product->store_quantity ?? 0) + (int) ($product->warehouse_quantity ?? 0);
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function demandRecords()
    {
        return $this->hasMany(DemandRecord::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        // <--- FIXED: Matches the SQL queries in your controllers
        $threshold = $this->reorder_point ?? 10;
        return $this->total_quantity <= $threshold;
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->store_quantity + $this->warehouse_quantity;
    }

    public function getStoreStatusAttribute(): string
    {
        if ($this->store_quantity <= 0) {
            return 'Out of Stock';
        }
        
        $threshold = $this->reorder_point ?? 10;
        if ($this->store_quantity <= $threshold) {
            return 'Low Stock';
        }
        
        return 'In Stock';
    }

    public function getWarehouseStatusAttribute(): string
    {
        if ($this->warehouse_quantity <= 0) {
            return 'Out of Stock';
        }
        
        $threshold = $this->reorder_point ?? 10;
        if ($this->warehouse_quantity <= $threshold) {
            return 'Low Stock';
        }
        
        return 'In Stock';
    }

    public function getTotalStatusAttribute(): string
    {
        if ($this->total_quantity <= 0) {
            return 'Out of Stock';
        }
        
        $threshold = $this->reorder_point ?? 10;
        if ($this->total_quantity <= $threshold) {
            return 'Low Stock';
        }
        
        return 'In Stock';
    }

    public function orderingCosts()
    {
        return $this->hasMany(ProductOrderingCost::class);
    }

    public function latestOrderingCost()
    {
        return $this->hasOne(ProductOrderingCost::class)->latestOfMany();
    }
}