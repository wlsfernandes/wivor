<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Stripe Checkout cannot charge less than $0.50 for a photo. */
    private const MINIMUM_PRICE_CENTS = 50;

    /** The old bundle discount applied from three photos. */
    private const PACKET_MINIMUM_PHOTOS = 3;

    /**
     * Keep today's buyer prices: every priced event whose bundle discount (its own override, or the
     * global default when it has none) is above 0% gets a 3-photo package price equal to the old
     * discounted price, rounded exactly as PhotoPricingService rounded it.
     *
     * events.packet_discount_percentage is left in place, unused, so this can be checked or redone.
     */
    public function up(): void
    {
        $defaultPercentage = (float) config('commission.packet_percentage');
        $now = now();

        DB::table('events')
            ->where('price_cents', '>', 0)
            ->select(['id', 'price_cents', 'packet_discount_percentage'])
            ->chunkById(500, function ($events) use ($defaultPercentage, $now): void {
                $tiers = [];

                foreach ($events as $event) {
                    $percentage = $event->packet_discount_percentage !== null
                        ? (float) $event->packet_discount_percentage
                        : $defaultPercentage;
                    $priceCents = (int) $event->price_cents;
                    $packagePriceCents = $priceCents - (int) round($priceCents * $percentage / 100);

                    if ($percentage <= 0 || $packagePriceCents >= $priceCents || $packagePriceCents < self::MINIMUM_PRICE_CENTS) {
                        continue;
                    }

                    $tiers[] = [
                        'event_id' => $event->id,
                        'min_photos' => self::PACKET_MINIMUM_PHOTOS,
                        'price_cents' => $packagePriceCents,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($tiers !== []) {
                    DB::table('event_price_tiers')->insertOrIgnore($tiers);
                }
            });
    }

    /** No package prices existed before this migration, so rolling back removes them all. */
    public function down(): void
    {
        DB::table('event_price_tiers')->delete();
    }
};
