<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public const FULFILLMENT_MANUAL = 'manual';
    public const FULFILLMENT_INSTANT = 'instant';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'duration_label',
        'fulfillment_type',
        'price',
        'stock_qty',
        'low_stock_threshold',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock_qty' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(ProductCredential::class);
    }

    public function availableCredentials(): HasMany
    {
        return $this->hasMany(ProductCredential::class)->where('is_used', false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_qty', '>', 0);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp' . number_format($this->price, 0, ',', '.');
    }

    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->low_stock_threshold;
    }

    public function isInstant(): bool
    {
        return $this->fulfillment_type === self::FULFILLMENT_INSTANT;
    }

    public function isManual(): bool
    {
        return $this->fulfillment_type === self::FULFILLMENT_MANUAL;
    }

    public function syncStockFromCredentials(): void
    {
        if ($this->isInstant()) {
            $availableCount = $this->availableCredentials()->count();
            $this->update(['stock_qty' => $availableCount]);
        }
    }
}
