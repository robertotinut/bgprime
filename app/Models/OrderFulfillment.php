<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFulfillment extends Model
{
    use HasFactory;

    public const SEND_PENDING = 'pending';
    public const SEND_SENT = 'sent';
    public const SEND_FAILED = 'failed';

    protected $fillable = [
        'order_id',
        'username',
        'password',
        'notes',
        'send_status',
        'sent_at',
        'resend_count',
        'last_resend_at',
    ];

    protected function casts(): array
    {
        return [
            'username' => 'encrypted',
            'password' => 'encrypted',
            'sent_at' => 'datetime',
            'last_resend_at' => 'datetime',
            'resend_count' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
