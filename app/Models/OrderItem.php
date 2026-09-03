<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public const DOWNLOAD_PENDING = 'pending';
    public const DOWNLOAD_READY = 'ready';
    public const DOWNLOAD_REVOKED = 'revoked';
    public const DOWNLOAD_EXPIRED = 'expired';

    protected $fillable = [
        'order_id', 'photo_id', 'photographer_id', 'photo_uuid', 'original_key',
        'unit_price_cents', 'download_status', 'download_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_cents' => 'integer',
            'download_expires_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function photo(): BelongsTo { return $this->belongsTo(Photo::class); }
    public function photographer(): BelongsTo { return $this->belongsTo(Photographer::class); }
}
