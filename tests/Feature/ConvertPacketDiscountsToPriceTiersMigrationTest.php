<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPriceTier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConvertPacketDiscountsToPriceTiersMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_bundle_discount_becomes_a_three_photo_package_at_the_same_buyer_price(): void
    {
        config(['commission.packet_percentage' => 20]);
        $defaultDiscountEvent = $this->event(1000, null);
        $roundedOverrideEvent = $this->event(995, 20);
        $decimalOverrideEvent = $this->event(1000, 12.5);

        $this->migration()->up();

        // 995 - round(199.0) = 796, the price PhotoPricingService charged for 3+ photos.
        $this->assertSame([3 => 800], $this->packagePrices($defaultDiscountEvent));
        $this->assertSame([3 => 796], $this->packagePrices($roundedOverrideEvent));
        $this->assertSame([3 => 875], $this->packagePrices($decimalOverrideEvent));
    }

    public function test_events_without_a_usable_discount_get_no_package(): void
    {
        config(['commission.packet_percentage' => 20]);
        $zeroOverrideEvent = $this->event(1000, 0);
        $unpricedEvent = $this->event(0, null);
        $belowStripeMinimumEvent = $this->event(60, 50);

        $this->migration()->up();

        $this->assertSame([], $this->packagePrices($zeroOverrideEvent));
        $this->assertSame([], $this->packagePrices($unpricedEvent));
        $this->assertSame([], $this->packagePrices($belowStripeMinimumEvent));
    }

    public function test_zero_global_default_converts_only_events_with_their_own_discount(): void
    {
        config(['commission.packet_percentage' => 0]);
        $defaultDiscountEvent = $this->event(1000, null);
        $overrideEvent = $this->event(1000, 25);

        $this->migration()->up();

        $this->assertSame([], $this->packagePrices($defaultDiscountEvent));
        $this->assertSame([3 => 750], $this->packagePrices($overrideEvent));
    }

    public function test_rolling_back_removes_the_package_prices(): void
    {
        config(['commission.packet_percentage' => 20]);
        $this->event(1000, null);
        $migration = $this->migration();

        $migration->up();
        $migration->down();

        $this->assertSame(0, EventPriceTier::count());
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_10_18_030000_convert_packet_discounts_to_price_tiers.php');
    }

    private function event(int $priceCents, float|int|null $packetDiscountPercentage): Event
    {
        $event = Event::create([
            'title' => 'Legacy Race '.uniqid(),
            'slug' => Event::generateUniqueSlug('Legacy Race '.uniqid()),
            'content' => '',
            'status' => Event::STATUS_PUBLISHED,
            'price_cents' => $priceCents,
        ]);

        // The column is no longer fillable; write it the way existing production rows hold it.
        DB::table('events')->where('id', $event->id)->update(['packet_discount_percentage' => $packetDiscountPercentage]);

        return $event;
    }

    /** @return array<int, int> */
    private function packagePrices(Event $event): array
    {
        return $event->priceTiers()->pluck('price_cents', 'min_photos')->all();
    }
}
