<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPriceTier;

/** Saves an event's package prices from validated form input. */
class EventPriceTierService
{
    /**
     * Replace the event's package prices with the given ones. Sizes left empty are removed.
     * Call inside the transaction that saves the event.
     *
     * @param  array<int|string, string|null>  $packagePrices  package size => price in dollars, as submitted
     */
    public function sync(Event $event, array $packagePrices): void
    {
        $packagePriceCents = collect(EventPriceTier::PACKAGE_SIZES)
            ->mapWithKeys(fn (int $minPhotos) => [$minPhotos => $packagePrices[$minPhotos] ?? null])
            ->reject(fn (?string $packagePrice) => blank($packagePrice))
            ->map(fn (string $packagePrice) => (int) round(((float) $packagePrice) * 100));

        $event->priceTiers()->whereNotIn('min_photos', $packagePriceCents->keys()->all())->delete();

        foreach ($packagePriceCents as $minPhotos => $priceCents) {
            $event->priceTiers()->updateOrCreate(['min_photos' => $minPhotos], ['price_cents' => $priceCents]);
        }

        $event->unsetRelation('priceTiers');
    }
}
