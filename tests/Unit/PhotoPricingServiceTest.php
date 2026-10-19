<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\EventPriceTier;
use App\Models\Order;
use App\Models\PromoCode;
use App\Services\PhotoPricingService;
use Tests\TestCase;

class PhotoPricingServiceTest extends TestCase
{
    public function test_percentage_label_drops_trailing_zeros_but_keeps_decimals(): void
    {
        $this->assertSame('20%', PhotoPricingService::percentageLabel('20.00'));
        $this->assertSame('12.5%', PhotoPricingService::percentageLabel(12.5));
        $this->assertSame('0.25%', PhotoPricingService::percentageLabel(0.25));
        $this->assertSame('0%', PhotoPricingService::percentageLabel(0));
    }

    public function test_money_label_formats_cents_as_us_dollars(): void
    {
        $this->assertSame('$17.00', PhotoPricingService::moneyLabel(1700));
        $this->assertSame('$0.50', PhotoPricingService::moneyLabel(50));
        $this->assertSame('$1,234.56', PhotoPricingService::moneyLabel(123456));
    }

    public function test_every_photo_costs_the_price_of_the_highest_package_reached(): void
    {
        $event = $this->event(1700, [2 => 1500, 3 => 1400, 4 => 1300, 5 => 1200, 10 => 1000]);
        $pricing = app(PhotoPricingService::class);

        $expectedUnitPrices = [1 => 1700, 2 => 1500, 3 => 1400, 4 => 1300, 5 => 1200, 9 => 1200, 10 => 1000, 25 => 1000];

        foreach ($expectedUnitPrices as $photoCount => $unitPriceCents) {
            $quote = $pricing->quote($event, $photoCount);

            $this->assertSame($unitPriceCents, $quote->unitPriceCents, "{$photoCount} photos");
            $this->assertSame($unitPriceCents * $photoCount, $quote->totalCents(), "{$photoCount} photos");
            $this->assertSame(1700 * $photoCount, $quote->subtotalCents(), "{$photoCount} photos");
        }
    }

    public function test_empty_packages_use_the_price_of_the_package_below(): void
    {
        $event = $this->event(1700, [2 => 1500, 5 => 1200]);
        $pricing = app(PhotoPricingService::class);

        $this->assertSame(1500, $pricing->quote($event, 3)->unitPriceCents);
        $this->assertSame(1500, $pricing->quote($event, 4)->unitPriceCents);
        $this->assertSame(1200, $pricing->quote($event, 12)->unitPriceCents);
    }

    public function test_package_quote_records_the_package_size_and_no_percentage(): void
    {
        $quote = app(PhotoPricingService::class)->quote($this->event(1700, [2 => 1500, 5 => 1200]), 3);

        $this->assertSame(Order::DISCOUNT_TIER, $quote->discountSource);
        $this->assertSame(2, $quote->priceTierMinPhotos);
        $this->assertNull($quote->discountPercentage);
        $this->assertSame(600, $quote->discountCents());
    }

    public function test_one_photo_or_no_packages_gives_the_single_photo_price_without_discount(): void
    {
        $pricing = app(PhotoPricingService::class);

        $singleQuote = $pricing->quote($this->event(1700, [2 => 1500]), 1);
        $noPackageQuote = $pricing->quote($this->event(1700), 6);

        foreach ([$singleQuote, $noPackageQuote] as $quote) {
            $this->assertNull($quote->discountSource);
            $this->assertNull($quote->priceTierMinPhotos);
            $this->assertSame(1700, $quote->unitPriceCents);
            $this->assertSame(0, $quote->discountCents());
        }
    }

    public function test_package_equal_to_the_single_photo_price_is_not_a_discount(): void
    {
        $pricing = app(PhotoPricingService::class);
        $event = $this->event(1000, [2 => 1000, 3 => 800]);

        $twoPhotoQuote = $pricing->quote($event, 2);

        $this->assertNull($twoPhotoQuote->discountSource);
        $this->assertSame(1000, $twoPhotoQuote->unitPriceCents);
        $this->assertSame(800, $pricing->quote($event, 3)->unitPriceCents);
    }

    public function test_next_package_must_beat_the_applied_promo_price(): void
    {
        $pricing = app(PhotoPricingService::class);
        $event = $this->event(1000, [3 => 900, 10 => 400]);

        $promoQuote = $pricing->quote($event, 2, new PromoCode(['discount_percent' => 50]));

        $this->assertSame(10, $pricing->nextPriceTier($event, $promoQuote)?->min_photos);
    }

    public function test_commission_is_taken_from_the_package_price(): void
    {
        config(['commission.percentage' => 20]);

        $quote = app(PhotoPricingService::class)->quote($this->event(1700, [2 => 1500]), 2);

        $this->assertSame(300, $quote->itemCommissionCents);
        $this->assertSame(1200, $quote->itemAllocationCents);
    }

    public function test_larger_promo_code_beats_the_package_price(): void
    {
        $promoCode = new PromoCode(['discount_percent' => 30]);

        $quote = app(PhotoPricingService::class)->quote($this->event(1000, [3 => 800]), 3, $promoCode);

        $this->assertSame(Order::DISCOUNT_PROMO, $quote->discountSource);
        $this->assertEquals(30, $quote->discountPercentage);
        $this->assertNull($quote->priceTierMinPhotos);
        $this->assertSame(700, $quote->unitPriceCents);
    }

    public function test_package_price_beats_a_smaller_promo_code_and_they_never_stack(): void
    {
        $promoCode = new PromoCode(['discount_percent' => 10]);

        $quote = app(PhotoPricingService::class)->quote($this->event(1000, [3 => 800]), 3, $promoCode);

        $this->assertSame(Order::DISCOUNT_TIER, $quote->discountSource);
        $this->assertSame(800, $quote->unitPriceCents);
    }

    public function test_promo_code_wins_when_it_equals_the_package_price(): void
    {
        $promoCode = new PromoCode(['discount_percent' => 20]);

        $quote = app(PhotoPricingService::class)->quote($this->event(1000, [3 => 800]), 3, $promoCode);

        $this->assertSame(Order::DISCOUNT_PROMO, $quote->discountSource);
        $this->assertSame(800, $quote->unitPriceCents);
    }

    public function test_promo_discount_and_commission_round_to_whole_cents(): void
    {
        config(['commission.percentage' => 20]);
        $promoCode = new PromoCode(['discount_percent' => 20]);

        $quote = app(PhotoPricingService::class)->quote($this->event(995), 3, $promoCode);

        // 995 - round(199.0) = 796; commission round(159.2) = 159; allocation 637.
        $this->assertSame(796, $quote->unitPriceCents);
        $this->assertSame(159, $quote->itemCommissionCents);
        $this->assertSame(637, $quote->itemAllocationCents);
        $this->assertSame(2985, $quote->subtotalCents());
        $this->assertSame(2388, $quote->totalCents());
    }

    public function test_next_package_names_the_photos_needed_and_its_price(): void
    {
        $event = $this->event(1700, [2 => 1500, 5 => 1200, 10 => 1000]);
        $pricing = app(PhotoPricingService::class);

        $this->assertSame([2, 1500], $this->nextPackage($pricing, $event, 1));
        $this->assertSame([5, 1200], $this->nextPackage($pricing, $event, 2));
        $this->assertSame([5, 1200], $this->nextPackage($pricing, $event, 4));
        $this->assertSame([10, 1000], $this->nextPackage($pricing, $event, 5));
        $this->assertNull($this->nextPackage($pricing, $event, 10));
        $this->assertNull($this->nextPackage($pricing, $this->event(1700), 1));
    }

    /** @return array{int, int}|null the next package's size and price */
    private function nextPackage(PhotoPricingService $pricing, Event $event, int $photoCount): ?array
    {
        $tier = $pricing->nextPriceTier($event, $pricing->quote($event, $photoCount));

        return $tier ? [$tier->min_photos, $tier->price_cents] : null;
    }

    /** @param array<int, int> $packagePrices package size => price in cents */
    private function event(int $priceCents, array $packagePrices = []): Event
    {
        $event = new Event(['price_cents' => $priceCents]);
        $event->setRelation('priceTiers', collect($packagePrices)->map(
            fn (int $packagePriceCents, int $minPhotos) => new EventPriceTier(['min_photos' => $minPhotos, 'price_cents' => $packagePriceCents]),
        )->values());

        return $event;
    }
}
