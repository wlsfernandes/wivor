<?php

namespace Tests\Unit;

use App\Models\Event;
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

    public function test_promo_code_wins_when_it_equals_the_packet_discount(): void
    {
        config(['commission.packet_percentage' => 20]);
        $promoCode = new PromoCode(['discount_percent' => 20]);

        $quote = app(PhotoPricingService::class)->quote($this->event(1000), 3, $promoCode);

        $this->assertSame(Order::DISCOUNT_PROMO, $quote->discountSource);
        $this->assertSame(800, $quote->unitPriceCents);
    }

    public function test_zero_packet_discount_with_three_photos_gives_no_discount(): void
    {
        config(['commission.packet_percentage' => 0]);

        $quote = app(PhotoPricingService::class)->quote($this->event(1000), 3);

        $this->assertNull($quote->discountSource);
        $this->assertSame(1000, $quote->unitPriceCents);
        $this->assertSame(0, $quote->discountCents());
    }

    public function test_discounted_unit_price_and_commission_round_to_whole_cents(): void
    {
        config(['commission.percentage' => 20, 'commission.packet_percentage' => 20]);

        $quote = app(PhotoPricingService::class)->quote($this->event(995), 3);

        // 995 - round(199.0) = 796; commission round(159.2) = 159; allocation 637.
        $this->assertSame(796, $quote->unitPriceCents);
        $this->assertSame(159, $quote->itemCommissionCents);
        $this->assertSame(637, $quote->itemAllocationCents);
        $this->assertSame(2985, $quote->subtotalCents());
        $this->assertSame(2388, $quote->totalCents());
    }

    public function test_no_photos_are_needed_for_the_packet_discount_once_three_are_selected(): void
    {
        config(['commission.packet_percentage' => 20]);
        $pricing = app(PhotoPricingService::class);
        $event = $this->event(1000);

        $this->assertSame(2, $pricing->photosNeededForPacketDiscount($event, 1));
        $this->assertSame(0, $pricing->photosNeededForPacketDiscount($event, 3));
        $this->assertSame(0, $pricing->photosNeededForPacketDiscount($event, 5));
    }

    private function event(int $priceCents): Event
    {
        return new Event(['price_cents' => $priceCents]);
    }
}
