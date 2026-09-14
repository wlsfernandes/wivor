<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Photo;
use App\Models\PromoCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

/** Manages the guest checkout selection for one event at a time. */
class CartService
{
    private const SESSION_KEY = 'wivor_cart';
    private const PROMO_CODE_SESSION_KEY = 'wivor_promo_code_id';

    /** Add a photo to the cart, enforcing the single-event rule. */
    public function add(Photo $photo): void
    {
        $photo->loadMissing('event');

        if ($photo->status !== Photo::STATUS_PUBLISHED
            || ! $photo->event->isSellable()) {
            throw ValidationException::withMessages([
                'photo' => 'This photo is not currently available for purchase.',
            ]);
        }

        $current = $this->photos()->first();

        if ($current && $current->event_id !== $photo->event_id) {
            throw ValidationException::withMessages([
                'photo' => 'You already selected photos from another event. Complete that purchase or clear your selection before choosing photos from this event.',
            ]);
        }

        Session::put(self::SESSION_KEY, $this->photoIds()->push($photo->uuid)->unique()->values()->all());
    }

    /** Remove a single photo from the cart. */
    public function remove(string $photoUuid): void
    {
        Session::put(self::SESSION_KEY, $this->photoIds()->reject(fn (string $uuid) => $uuid === $photoUuid)->values()->all());
    }

    /** Empty the cart entirely. */
    public function clear(): void
    {
        Session::forget([self::SESSION_KEY, self::PROMO_CODE_SESSION_KEY]);
    }

    /** Return the selected photos that are still eligible for purchase, pruning any that are not. */
    public function photos(): Collection
    {
        $ids = $this->photoIds();

        if ($ids->isEmpty()) {
            return collect();
        }

        $photos = Photo::query()
            ->with('event')
            ->whereIn('uuid', $ids->all())
            ->where('status', Photo::STATUS_PUBLISHED)
            ->get()
            ->filter(fn (Photo $photo) => $photo->event->isSellable());

        // The first surviving item establishes the cart's single event; any photographer may contribute.
        $first = $photos->first();
        if ($first) {
            $photos = $photos->filter(fn (Photo $photo) => $photo->event_id === $first->event_id);
        }

        $validIds = $photos->pluck('uuid');
        if ($validIds->count() !== $ids->count()) {
            Session::put(self::SESSION_KEY, $validIds->values()->all());
        }

        return $photos->values();
    }

    /** Return the event shared by every item in the cart. */
    public function event(): ?Event
    {
        return $this->photos()->first()?->event;
    }

    public function count(): int
    {
        return $this->photos()->count();
    }

    /** Recompute the subtotal from the current event price, never from stored values. */
    public function subtotalCents(): int
    {
        $event = $this->event();

        return $event ? $this->count() * $event->price_cents : 0;
    }

    /** Store only the applied promo code reference in the cart session. */
    public function applyPromoCode(PromoCode $promoCode): void
    {
        Session::put(self::PROMO_CODE_SESSION_KEY, $promoCode->id);
    }

    /** Remove the applied promo code from the cart session. */
    public function removePromoCode(): void
    {
        Session::forget(self::PROMO_CODE_SESSION_KEY);
    }

    /** Determine whether the session currently contains a promo code reference. */
    public function hasPromoCode(): bool
    {
        return Session::has(self::PROMO_CODE_SESSION_KEY);
    }

    /** Retrieve the applied promo code only while it remains active and unexpired. */
    public function promoCode(): ?PromoCode
    {
        $promoCodeId = Session::get(self::PROMO_CODE_SESSION_KEY);

        if (! $promoCodeId) {
            return null;
        }

        $promoCode = PromoCode::query()
            ->whereKey($promoCodeId)
            ->where('is_active', true)
            ->whereDate('expires_at', '>=', today())
            ->first();

        if (! $promoCode) {
            $this->removePromoCode();
        }

        return $promoCode;
    }

    /** Calculate the applied discount in cents using the current per-photo price. */
    public function discountAmountCents(?PromoCode $promoCode = null): int
    {
        $promoCode ??= $this->promoCode();
        $event = $this->event();

        if (! $promoCode || ! $event) {
            return 0;
        }

        $discountPerPhotoCents = (int) round($event->price_cents * $promoCode->discount_percent / 100);

        return $discountPerPhotoCents * $this->count();
    }

    /** Calculate the current cart total after the applied promo discount. */
    public function totalCents(?PromoCode $promoCode = null): int
    {
        return max($this->subtotalCents() - $this->discountAmountCents($promoCode), 0);
    }

    private function photoIds(): Collection
    {
        return collect(Session::get(self::SESSION_KEY, []));
    }
}
