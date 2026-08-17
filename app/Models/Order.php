<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_WAITING_CONFIRMATION = 'waiting_confirmation';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REJECTED = 'rejected';
    public const PAYMENT_REFUNDED = 'refunded';

    public const ORDER_WAITING_PAYMENT = 'waiting_payment';
    public const ORDER_PROCESSING = 'processing';
    public const ORDER_COMPLETED = 'completed';
    public const ORDER_CANCELLED = 'cancelled';
    public const ORDER_FAILED = 'failed';

    public const FULFILLMENT_PENDING = 'pending';
    public const FULFILLMENT_WAITING = 'waiting';
    public const FULFILLMENT_PROCESSING = 'processing';
    public const FULFILLMENT_SENT = 'sent';
    public const FULFILLMENT_FAILED = 'failed';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'product_id',
        'product_name',
        'product_price',
        'amount',
        'payment_status',
        'order_status',
        'fulfillment_status',
        'paid_at',
        'fulfilled_at',
    ];

    protected function casts(): array
    {
        return [
            'product_price' => 'integer',
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'fulfilled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function fulfillment(): HasOne
    {
        return $this->hasOne(OrderFulfillment::class);
    }

    public function stockMovement(): HasOne
    {
        return $this->hasOne(StockMovement::class);
    }

    public function supportTicket(): HasOne
    {
        return $this->hasOne(SupportTicket::class);
    }

    public function scopeWaitingConfirmation(Builder $query): Builder
    {
        return $query->where('payment_status', self::PAYMENT_WAITING_CONFIRMATION);
    }

    public function scopeNeedFulfillment(Builder $query): Builder
    {
        return $query->where('payment_status', self::PAYMENT_PAID)
            ->whereIn('fulfillment_status', [self::FULFILLMENT_WAITING, self::FULFILLMENT_PROCESSING]);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp' . number_format($this->amount, 0, ',', '.');
    }

    public function getCustomerStatusLabelAttribute(): string
    {
        if ($this->order_status === self::ORDER_CANCELLED) {
            return '❌ Pesanan Dibatalkan';
        }

        if ($this->payment_status === self::PAYMENT_REJECTED) {
            return '❌ Pembayaran Ditolak';
        }

        if ($this->payment_status === self::PAYMENT_REFUNDED) {
            return '↩️ Dana Dikembalikan';
        }

        if ($this->order_status === self::ORDER_COMPLETED || $this->fulfillment_status === self::FULFILLMENT_SENT) {
            return '✅ Pesanan Selesai';
        }

        if ($this->payment_status === self::PAYMENT_WAITING_CONFIRMATION) {
            return '🔎 Pembayaran Sedang Dicek';
        }

        if ($this->payment_status === self::PAYMENT_PAID || $this->order_status === self::ORDER_PROCESSING) {
            return '📦 Pesanan Sedang Diproses';
        }

        return '⏳ Menunggu Pembayaran';
    }
}
