<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPriceTier;
use App\Models\Order;
use App\Models\PromoCode;

/**
 * Calculates per-photo prices, discounts, commission, and photographer allocation.
 *
 * The cart and checkout both use this class so the displayed total always matches the Stripe total.
 * Every photo in an order costs the same unit price: the price of the largest package the order
 * reaches, or the single-photo price when it reaches none. A promo code is a percentage off the
 * single-photo price; only the lower of the promo and package prices applies, they never stack, and
 * the promo wins a tie. Commission is taken from the price paid, so WivorPhotos and the photographer
 * share any discount proportionally.
 */
class PhotoPricingService
{
    public function quote(Event $event, int $photoCount, ?PromoCode $promoCode = null): PriceQuote
    {
        $listUnitPriceCents = (int) $event->price_cents;
        $priceTier = $this->priceTierFor($event, $photoCount);
        $promoPercentage = $promoCode ? (float) $promoCode->discount_percent : 0.0;
        $promoUnitPriceCents = max($listUnitPriceCents - (int) round($listUnitPriceCents * $promoPercentage / 100), 0);

        [$discountSource, $unitPriceCents] = match (true) {
            $promoPercentage > 0 && (! $priceTier || $promoUnitPriceCents <= $priceTier->price_cents) => [Order::DISCOUNT_PROMO, $promoUnitPriceCents],
            $priceTier !== null => [Order::DISCOUNT_TIER, $priceTier->price_cents],
            default => [null, $listUnitPriceCents],
        };

        $commissionPercentage = $event->effectiveCommissionPercentage();
        $itemCommissionCents = (int) round($unitPriceCents * $commissionPercentage / 100);

        return new PriceQuote(
            photoCount: $photoCount,
            listUnitPriceCents: $listUnitPriceCents,
            unitPriceCents: $unitPriceCents,
            discountSource: $discountSource,
            discountPercentage: $discountSource === Order::DISCOUNT_PROMO ? $promoPercentage : null,
            priceTierMinPhotos: $discountSource === Order::DISCOUNT_TIER ? $priceTier->min_photos : null,
            commissionPercentage: $commissionPercentage,
            itemCommissionCents: $itemCommissionCents,
            itemAllocationCents: $unitPriceCents - $itemCommissionCents,
        );
    }

    /**
     * Return the next package that is cheaper than the price the quote already charges, including
     * any promo code, or null when no larger order would lower the per-photo price.
     */
    public function nextPriceTier(Event $event, PriceQuote $quote): ?EventPriceTier
    {
        return $event->priceTiers
            ->filter(fn (EventPriceTier $tier) => $tier->min_photos > $quote->photoCount && $tier->price_cents < $quote->unitPriceCents)
            ->sortBy('min_photos')
            ->first();
    }

    /** Format a percentage without trailing zeros, for example 20.00 as "20%" and 12.50 as "12.5%". */
    public static function percentageLabel(float|string $percentage): string
    {
        return rtrim(rtrim(number_format((float) $percentage, 2, '.', ''), '0'), '.').'%';
    }

    /** Format an amount in cents as US dollars, for example 1500 as "$15.00". */
    public static function moneyLabel(int $amountCents): string
    {
        return '$'.number_format($amountCents / 100, 2);
    }

    /** Return the largest package the order reaches that is cheaper than one photo, or null when it reaches none. */
    private function priceTierFor(Event $event, int $photoCount): ?EventPriceTier
    {
        return $event->priceTiers
            ->filter(fn (EventPriceTier $tier) => $tier->min_photos <= $photoCount && $tier->price_cents < (int) $event->price_cents)
            ->sortByDesc('min_photos')
            ->first();
    }
}
