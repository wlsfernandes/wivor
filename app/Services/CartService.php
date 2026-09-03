<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

/** Manages the guest checkout selection: one event and one photographer per cart. */
class CartService
{
    private const SESSION_KEY = 'wivor_cart';

    /** Add a photo to the cart, enforcing the single event/photographer rule. */
    public function add(Photo $photo): void
    {
        if ($photo->status !== Photo::STATUS_PUBLISHED || ! $photo->event->isSellable()) {
            throw ValidationException::withMessages([
                'photo' => 'This photo is not currently available for purchase.',
            ]);
        }

        $current = $this->photos()->first();

        if ($current && ($current->event_id !== $photo->event_id || $current->photographer_id !== $photo->photographer_id)) {
            throw ValidationException::withMessages([
                'photo' => 'Your current order contains photos from another photographer. Complete that purchase or clear your current selection before adding this photo.',
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
        Session::forget(self::SESSION_KEY);
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

        // The first surviving item establishes the cart's single event/photographer pair.
        $first = $photos->first();
        if ($first) {
            $photos = $photos->filter(fn (Photo $photo) => $photo->event_id === $first->event_id
                && $photo->photographer_id === $first->photographer_id);
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

    private function photoIds(): Collection
    {
        return collect(Session::get(self::SESSION_KEY, []));
    }
}
