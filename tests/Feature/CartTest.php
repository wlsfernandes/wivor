<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\PromoCode;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_and_remove_a_photo_from_the_cart(): void
    {
        [$event, $photo] = $this->publishedPhoto();

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid])->assertRedirect();
        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSee('1 photo selected')
            ->assertSee('View cart with 1 selected photo')
            ->assertSee('cart-count-badge', false)
            ->assertSeeInOrder(['Subtotal', '$10.00', 'Total', '$10.00']);

        $this->delete(route('cart.items.destroy', ['photo' => $photo->uuid]))->assertRedirect();
        $this->get(route('cart.show'))
            ->assertSee('You have not selected any photos yet.')
            ->assertDontSee('cart-count-badge', false);
    }

    public function test_cart_allows_photos_from_multiple_photographers_in_the_same_event(): void
    {
        $event = $this->publishedEvent();
        $photoOne = $this->publishedPhotoForEvent($event);
        $photoTwo = $this->publishedPhotoForEvent($event);

        $this->post(route('cart.items.store'), ['photo' => $photoOne->uuid])->assertRedirect();
        $this->post(route('cart.items.store'), ['photo' => $photoTwo->uuid])->assertRedirect()->assertSessionDoesntHaveErrors();

        $this->get(route('cart.show'))->assertSee('2 photos selected');
    }

    public function test_cart_rejects_a_photo_from_a_different_event(): void
    {
        [, $photoOne] = $this->publishedPhoto();
        [, $photoTwo] = $this->publishedPhoto();

        $this->post(route('cart.items.store'), ['photo' => $photoOne->uuid])->assertRedirect();
        $this->post(route('cart.items.store'), ['photo' => $photoTwo->uuid])
            ->assertSessionHasErrors('photo');

        $this->get(route('cart.show'))->assertSee('1 photo selected');
    }

    public function test_cart_allows_an_approved_photo_before_the_photographer_finishes_stripe_setup(): void
    {
        $event = $this->publishedEvent();
        $photo = $this->publishedPhotoForEvent($event, stripeReady: false);

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid])
            ->assertSessionDoesntHaveErrors();

        $this->get(route('cart.show'))->assertSee('1 photo selected');
    }

    public function test_cart_subtotal_reflects_the_current_event_price(): void
    {
        [$event, $photo] = $this->publishedPhoto();
        $this->post(route('cart.items.store'), ['photo' => $photo->uuid])->assertRedirect();

        $event->update(['price_cents' => 1500]);

        $this->get(route('cart.show'))->assertSeeInOrder(['Subtotal', '$15.00', 'Total', '$15.00']);
    }

    public function test_guest_can_apply_and_remove_a_valid_promo_code(): void
    {
        [, $photo] = $this->publishedPhoto();
        $promoCode = PromoCode::create([
            'code' => 'SUMMER20',
            'discount_percent' => 20,
            'is_active' => true,
            'expires_at' => today(),
        ]);
        $this->post(route('cart.items.store'), ['photo' => $photo->uuid]);

        $this->postJson(route('cart.promo-code.store'), ['promo_code' => ' SUMMER20 '])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'code' => 'SUMMER20',
                'discount_percent' => 20,
                'subtotal' => '$10.00',
                'discount' => '$2.00',
                'total' => '$8.00',
            ]);

        $this->assertSame($promoCode->id, session('wivor_promo_code_id'));
        $this->get(route('cart.show'))
            ->assertSee('SUMMER20 applied - 20% off')
            ->assertSeeInOrder(['Subtotal', '$10.00', 'Promo', 'SUMMER20', '$2.00', 'Total', '$8.00']);

        $this->deleteJson(route('cart.promo-code.destroy'))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'discount' => '$0.00',
                'total' => '$10.00',
            ]);
        $this->assertNull(session('wivor_promo_code_id'));
    }

    public function test_cart_tells_buyers_how_many_photos_reach_the_next_package_price(): void
    {
        $event = $this->publishedEventWithPackagePrices([3 => 800]);

        $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);

        $this->get(route('cart.show'))
            ->assertSee('Add 2 more photos to pay $8.00 each.')
            ->assertSeeInOrder(['Subtotal', '$10.00', 'Total', '$10.00']);
    }

    public function test_cart_hint_uses_singular_when_one_more_photo_reaches_the_package_price(): void
    {
        $event = $this->publishedEventWithPackagePrices([3 => 800]);

        foreach (range(1, 2) as $ignored) {
            $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);
        }

        $this->get(route('cart.show'))->assertSee('Add 1 more photo to pay $8.00 each.');
    }

    public function test_cart_hint_points_to_the_next_larger_package_once_one_is_reached(): void
    {
        $event = $this->publishedEventWithPackagePrices([2 => 900, 5 => 750]);

        foreach (range(1, 2) as $ignored) {
            $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);
        }

        $this->get(route('cart.show'))
            ->assertSee('Add 3 more photos to pay $7.50 each.')
            ->assertSeeInOrder(['Subtotal', '$20.00', 'Package price (2+ photos)', '$2.00', 'Total', '$18.00']);
    }

    public function test_cart_hint_only_offers_packages_cheaper_than_the_applied_promo_price(): void
    {
        $event = $this->publishedEventWithPackagePrices([3 => 900, 10 => 400]);
        PromoCode::create(['code' => 'HALF', 'discount_percent' => 50, 'is_active' => true, 'expires_at' => today()]);

        foreach (range(1, 2) as $ignored) {
            $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);
        }
        $this->postJson(route('cart.promo-code.store'), ['promo_code' => 'HALF']);

        // The promo already gives $5.00 each, so the 3-photo $9.00 package is not offered.
        $this->get(route('cart.show'))
            ->assertDontSee('to pay $9.00 each')
            ->assertSee('Add 8 more photos to pay $4.00 each.');
    }

    public function test_promo_responses_return_the_package_hint_for_the_new_price(): void
    {
        $event = $this->publishedEventWithPackagePrices([3 => 900]);
        PromoCode::create(['code' => 'HALF', 'discount_percent' => 50, 'is_active' => true, 'expires_at' => today()]);
        $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);

        $this->postJson(route('cart.promo-code.store'), ['promo_code' => 'HALF'])
            ->assertOk()
            ->assertJson(['package_hint' => null]);

        $this->deleteJson(route('cart.promo-code.destroy'))
            ->assertOk()
            ->assertJson(['package_hint' => 'Add 2 more photos to pay $9.00 each.']);
    }

    public function test_cart_page_always_renders_the_package_hint_element_for_promo_updates(): void
    {
        [, $photo] = $this->publishedPhoto();
        $this->post(route('cart.items.store'), ['photo' => $photo->uuid]);

        $this->get(route('cart.show'))->assertSee('data-package-hint', false);
    }

    public function test_package_equal_to_the_single_photo_price_is_not_shown_as_a_discount(): void
    {
        $event = $this->publishedEventWithPackagePrices([2 => 1000, 3 => 800]);

        foreach (range(1, 2) as $ignored) {
            $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);
        }

        $this->get(route('cart.show'))
            ->assertDontSee('Package price (2+ photos)')
            ->assertSeeInOrder(['Subtotal', '$20.00', 'Total', '$20.00']);
        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertDontSee('2+ photos $10.00 each')
            ->assertSee('3+ photos $8.00 each');
    }

    public function test_event_and_gallery_pages_list_the_package_prices(): void
    {
        $event = $this->publishedEventWithPackagePrices([2 => 900, 5 => 750]);
        $this->publishedPhotoForEvent($event);

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertSee('$10.00 per photo')
            ->assertSee('2+ photos $9.00 each · 5+ photos $7.50 each');
        $this->get(route('events.photos.index', ['event' => $event->slug]))
            ->assertSee('2+ photos $9.00 each · 5+ photos $7.50 each');
    }

    public function test_event_pages_hide_the_package_line_when_the_event_has_no_package_prices(): void
    {
        [$event] = $this->publishedPhoto();

        $this->get(route('events.show', ['event' => $event->slug]))->assertDontSee('+ photos');
        $this->get(route('events.photos.index', ['event' => $event->slug]))->assertDontSee('+ photos');
    }

    public function test_removing_a_promo_code_keeps_the_package_price(): void
    {
        $event = $this->publishedEventWithPackagePrices([3 => 800]);
        PromoCode::create(['code' => 'BIG30', 'discount_percent' => 30, 'is_active' => true, 'expires_at' => today()]);

        foreach (range(1, 3) as $ignored) {
            $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);
        }
        $this->postJson(route('cart.promo-code.store'), ['promo_code' => 'BIG30'])
            ->assertJson(['discount_label' => 'Promo BIG30', 'total' => '$21.00']);

        $this->deleteJson(route('cart.promo-code.destroy'))
            ->assertOk()
            ->assertJson([
                'discount_label' => 'Package price (3+ photos)',
                'discount' => '$6.00',
                'total' => '$24.00',
            ]);
    }

    public function test_cart_page_says_the_package_price_applies_when_it_beats_the_promo_code(): void
    {
        $event = $this->publishedEventWithPackagePrices([3 => 800]);
        PromoCode::create(['code' => 'SMALL10', 'discount_percent' => 10, 'is_active' => true, 'expires_at' => today()]);

        foreach (range(1, 3) as $ignored) {
            $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);
        }
        $this->postJson(route('cart.promo-code.store'), ['promo_code' => 'SMALL10']);

        $this->get(route('cart.show'))
            ->assertSee('Your package price is lower than this promo code, so the package price applies.')
            ->assertDontSee('SMALL10 applied');
    }

    public function test_cart_applies_the_package_price_from_its_package_size(): void
    {
        $event = $this->publishedEventWithPackagePrices([3 => 800]);

        foreach (range(1, 3) as $ignored) {
            $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);
        }

        $this->get(route('cart.show'))
            ->assertDontSee('more photo')
            ->assertSeeInOrder(['Subtotal', '$30.00', 'Package price (3+ photos)', '$6.00', 'Total', '$24.00']);
    }

    public function test_smaller_promo_code_does_not_stack_on_the_package_price(): void
    {
        $event = $this->publishedEventWithPackagePrices([3 => 800]);
        PromoCode::create(['code' => 'SMALL10', 'discount_percent' => 10, 'is_active' => true, 'expires_at' => today()]);

        foreach (range(1, 3) as $ignored) {
            $this->post(route('cart.items.store'), ['photo' => $this->publishedPhotoForEvent($event)->uuid]);
        }

        $this->postJson(route('cart.promo-code.store'), ['promo_code' => 'SMALL10'])
            ->assertOk()
            ->assertJson([
                'discount_label' => 'Package price (3+ photos)',
                'discount' => '$6.00',
                'total' => '$24.00',
            ]);
    }

    public function test_cart_shows_no_package_hint_when_the_event_has_no_package_prices(): void
    {
        [, $photo] = $this->publishedPhoto();

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid]);

        $this->get(route('cart.show'))->assertDontSee('more photo');
    }

    public function test_invalid_inactive_and_expired_promo_codes_are_rejected(): void
    {
        PromoCode::create([
            'code' => 'INACTIVE',
            'discount_percent' => 20,
            'is_active' => false,
            'expires_at' => today()->addDay(),
        ]);
        PromoCode::create([
            'code' => 'EXPIRED',
            'discount_percent' => 20,
            'is_active' => true,
            'expires_at' => today()->subDay(),
        ]);

        foreach (['UNKNOWN', 'INACTIVE', 'EXPIRED'] as $promoCodeValue) {
            $this->postJson(route('cart.promo-code.store'), ['promo_code' => $promoCodeValue])
                ->assertUnprocessable()
                ->assertJson([
                    'success' => false,
                    'message' => 'This promo code is invalid or expired.',
                ]);
        }

        $this->assertNull(session('wivor_promo_code_id'));
    }

    /** @return array{Event, Photo} */
    private function publishedPhoto(array $eventOverrides = []): array
    {
        $event = $this->publishedEvent($eventOverrides);

        return [$event, $this->publishedPhotoForEvent($event)];
    }

    /** @param array<int, int> $packagePrices package size => price in cents; the single-photo price is $10.00 */
    private function publishedEventWithPackagePrices(array $packagePrices): Event
    {
        $event = $this->publishedEvent();

        foreach ($packagePrices as $minPhotos => $priceCents) {
            $event->priceTiers()->create(['min_photos' => $minPhotos, 'price_cents' => $priceCents]);
        }

        return $event;
    }

    private function publishedEvent(array $eventOverrides = []): Event
    {
        return Event::create(array_merge([
            'title' => 'City Run '.uniqid(),
            'slug' => Event::generateUniqueSlug('City Run '.uniqid()),
            'sport' => 'Running',
            'content' => 'A city road race.',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now()->subDay(),
            'date_of_event' => '2026-08-30',
            'sales_close_at' => now()->addMonth(),
            'price_cents' => 1000,
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
        ], $eventOverrides));
    }

    private function publishedPhotoForEvent(Event $event, bool $stripeReady = true): Photo
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
        ]);
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED]);
        if ($stripeReady) {
            $photographer->forceFill([
                'stripe_account_id' => 'acct_'.uniqid(),
                'stripe_onboarding_status' => Photographer::STRIPE_COMPLETE,
            ]);
        }
        $photographer->save();

        $assignment = EventAssignment::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'status' => 'approved',
            'upload_deadline_at' => now()->addDays(3),
        ]);
        $batch = UploadBatch::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'selected_count' => 1,
            'status' => 'completed',
        ]);

        return Photo::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => 'finish.jpg',
            'original_key' => 'photos/original-'.uniqid().'.jpg',
            'detected_mime' => 'image/jpeg',
            'width' => 2400,
            'height' => 1600,
            'checksum' => hash('sha256', uniqid('', true)),
            'status' => Photo::STATUS_PUBLISHED,
            'uploaded_at' => now()->subDay(),
            'published_at' => now()->subHours(12),
        ]);
    }
}
