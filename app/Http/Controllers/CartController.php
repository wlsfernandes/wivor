<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Handles the guest photo-selection cart used before Stripe checkout. */
class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    /** Display the current selection and subtotal. */
    public function index(Request $request): View
    {
        $checkoutToken = (string) Str::uuid();
        $request->session()->put('wivor_checkout_token', $checkoutToken);

        return view('cart.show', [
            'event' => $this->cart->event(),
            'photos' => $this->cart->photos(),
            'photoCountLabel' => $this->photoCountLabel($this->cart->count()),
            'subtotalLabel' => $this->moneyLabel($this->cart->subtotalCents()),
            'checkoutToken' => $checkoutToken,
            'layout' => 'layouts.app',
        ]);
    }

    /** Add a photo to the cart. */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'photo' => ['required', 'string', 'exists:photos,uuid'],
        ]);

        $photo = Photo::where('uuid', $validated['photo'])->firstOrFail();

        try {
            $this->cart->add($photo);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Photo added to your order.');
    }

    /** Remove a single photo from the cart. */
    public function destroy(Photo $photo): RedirectResponse
    {
        $this->cart->remove($photo->uuid);

        return back()->with('success', 'Photo removed from your order.');
    }

    /** Empty the cart. */
    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return back()->with('success', 'Your selection was cleared.');
    }

    /** Return a display-ready photo count. */
    private function photoCountLabel(int $count): string
    {
        return $count.' '.($count === 1 ? 'photo' : 'photos').' selected';
    }

    /** Return a display-ready US dollar amount. */
    private function moneyLabel(int $amountCents): string
    {
        return '$'.number_format($amountCents / 100, 2);
    }
}
