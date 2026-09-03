<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';
    public const PAYMENT_DISPUTED = 'disputed';
    public const PAYMENT_CANCELLED = 'cancelled_expired';

    public const FULFILLMENT_PENDING = 'pending';
    public const FULFILLMENT_READY = 'ready';
    public const FULFILLMENT_FAILED = 'failed';
    public const FULFILLMENT_REVOKED = 'revoked';
    public const FULFILLMENT_EXPIRED = 'expired';

    protected $fillable = [
        'order_number', 'access_token', 'event_id', 'photographer_id', 'customer_email',
        'currency', 'photo_count', 'unit_price_cents', 'subtotal_cents',
        'commission_percentage', 'commission_cents', 'photographer_allocation_cents',
        'stripe_fee_cents', 'total_cents', 'payment_status', 'fulfillment_status',
        'stripe_checkout_session_id', 'stripe_payment_intent_id', 'stripe_charge_id',
        'stripe_connected_account_id', 'stripe_application_fee_id',
        'paid_at', 'refunded_at', 'disputed_at', 'fulfilled_at', 'cancelled_at', 'download_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'photo_count' => 'integer',
            'unit_price_cents' => 'integer',
            'subtotal_cents' => 'integer',
            'commission_percentage' => 'decimal:2',
            'commission_cents' => 'integer',
            'photographer_allocation_cents' => 'integer',
            'stripe_fee_cents' => 'integer',
            'total_cents' => 'integer',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'disputed_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'download_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            $order->order_number ??= self::generateOrderNumber();
            $order->access_token ??= Str::random(64);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function photographer(): BelongsTo { return $this->belongsTo(Photographer::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }

    /** Generate a unique, non-sequential public order number. */
    public static function generateOrderNumber(): string
    {
        do {
            $candidate = 'WVR-'.Str::upper(Str::random(8));
        } while (self::where('order_number', $candidate)->exists());

        return $candidate;
    }

    /** Return the paid date, falling back to when the order was placed. */
    public function getSaleDateLabelAttribute(): string
    {
        return ($this->paid_at ?? $this->created_at)->format('M j, Y');
    }

    /** Return a display-ready payment status. */
    public function getPaymentStatusLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->payment_status));
    }

    /** Return the payout state; payout tracking is not yet ingested from Stripe, so paid orders are always pending. */
    public function getPayoutStatusLabelAttribute(): string
    {
        return $this->payment_status === self::PAYMENT_PAID ? 'Pending payout' : 'Not applicable';
    }

    /** Return the pre-commission sale amount formatted as US dollars. */
    public function getGrossAmountLabelAttribute(): string
    {
        return '$'.number_format($this->subtotal_cents / 100, 2);
    }

    /** Return the WivorPhotos commission formatted as US dollars. */
    public function getFeesLabelAttribute(): string
    {
        return '$'.number_format($this->commission_cents / 100, 2);
    }

    /** Return the photographer's net earnings formatted as US dollars. */
    public function getNetAmountLabelAttribute(): string
    {
        return '$'.number_format($this->photographer_allocation_cents / 100, 2);
    }
}
