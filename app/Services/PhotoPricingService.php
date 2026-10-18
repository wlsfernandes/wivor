<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\PromoCode;

/**
 * Calculates per-photo prices, discounts, commission, and photographer allocation.
 *
 * The cart and checkout both use this class so the displayed total always matches the Stripe total.
 * Only the larger of the promo-code and packet discounts applies; they never stack. The discount
 * lowers the unit price before commission, so WivorPhotos and the photographer share it proportionally.
 */
class PhotoPricingService
{
    /** The minimum number of photos in one order that unlocks the packet discount. */
    public const PACKET_MINIMUM_PHOTOS = 3;

    public function quote(Event $event, int $photoCount, ?PromoCode $promoCode = null): PriceQuote
    {
        [$discountSource, $discountPercentage] = $this->bestDiscount($event, $photoCount, $promoCode);
        $listUnitPriceCents = (int) $event->price_cents;
        $unitPriceCents = max($listUnitPriceCents - (int) round($listUnitPriceCents * $discountPercentage / 100), 0);
        $commissionPercentage = $event->effectiveCommissionPercentage();
        $itemCommissionCents = (int) round($unitPriceCents * $commissionPercentage / 100);

        return new PriceQuote(
            photoCount: $photoCount,
            listUnitPriceCents: $listUnitPriceCents,
            unitPriceCents: $unitPriceCents,
            discountSource: $discountSource,
            discountPercentage: $discountPercentage,
            commissionPercentage: $commissionPercentage,
            itemCommissionCents: $itemCommissionCents,
            itemAllocationCents: $unitPriceCents - $itemCommissionCents,
        );
    }

    /** Return how many more photos the customer needs to unlock the packet discount, or 0 when none applies. */
    public function photosNeededForPacketDiscount(Event $event, int $photoCount): int
    {
        if ($event->effectivePacketDiscountPercentage() <= 0) {
            return 0;
        }

        return max(self::PACKET_MINIMUM_PHOTOS - $photoCount, 0);
    }

    /** Format a percentage without trailing zeros, for example 20.00 as "20%" and 12.50 as "12.5%". */
    public static function percentageLabel(float|string $percentage): string
    {
        return rtrim(rtrim(number_format((float) $percentage, 2, '.', ''), '0'), '.').'%';
    }

    /** @return array{?string, float} the discount source and percentage that give the customer the lower price */
    private function bestDiscount(Event $event, int $photoCount, ?PromoCode $promoCode): array
    {
        $promoPercentage = $promoCode ? (float) $promoCode->discount_percent : 0.0;
        $packetPercentage = $photoCount >= self::PACKET_MINIMUM_PHOTOS ? $event->effectivePacketDiscountPercentage() : 0.0;

        if ($promoPercentage <= 0 && $packetPercentage <= 0) {
            return [null, 0.0];
        }

        return $promoPercentage >= $packetPercentage
            ? [Order::DISCOUNT_PROMO, $promoPercentage]
            : [Order::DISCOUNT_PACKET, $packetPercentage];
    }
}
