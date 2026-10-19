<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A package price: the price of every photo in an order once it reaches min_photos photos.
 *
 * @property int $event_id
 * @property int $min_photos
 * @property int $price_cents
 */
class EventPriceTier extends Model
{
    /** The order sizes that may have a package price. One photo always uses events.price_cents. */
    public const PACKAGE_SIZES = [2, 3, 4, 5, 10];

    protected $fillable = ['event_id', 'min_photos', 'price_cents'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'min_photos' => 'integer',
            'price_cents' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
