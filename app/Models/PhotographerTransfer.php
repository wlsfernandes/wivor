<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Records one Stripe transfer obligation for a photographer's share of a paid order.
 *
 * @property int $id
 * @property int $order_id
 * @property int $photographer_id
 * @property int $amount_cents
 * @property string $currency
 * @property string|null $stripe_account_id
 * @property string|null $stripe_transfer_id
 * @property string $status
 */
class PhotographerTransfer extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUCCEEDED = 'succeeded';

    protected $fillable = [
        'order_id',
        'photographer_id',
        'amount_cents',
        'currency',
        'stripe_account_id',
        'stripe_transfer_id',
        'status',
        'last_error',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'transferred_at' => 'datetime',
        ];
    }

    /** Return the paid order that created this transfer obligation. */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Return the photographer who receives this transfer. */
    public function photographer(): BelongsTo
    {
        return $this->belongsTo(Photographer::class);
    }
}
