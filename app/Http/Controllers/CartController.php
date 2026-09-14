<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\PromoCode;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
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
        $promoCode = $this->cart->promoCode();
        $discountAmountCents = $this->cart->discountAmountCents($promoCode);

        return view('cart.show', [
            'event' => $this->cart->event(),
            'photos' => $this->cart->photos(),
            'photoCountLabel' => $this->photoCountLabel($this->cart->count()),
            'subtotalLabel' => $this->moneyLabel($this->cart->subtotalCents()),
            'promoCode' => $promoCode,
            'discountLabel' => $this->moneyLabel($discountAmountCents),
            'totalLabel' => $this->moneyLabel($this->cart->totalCents($promoCode)),
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

    /** Validate and apply a promo code to the current cart. */
    public function applyPromoCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'promo_code' => ['required', 'string', 'max:50'],
        ]);
        $promoCodeValue = trim($validated['promo_code']);

        $promoCode = PromoCode::query()
            ->where('code', $promoCodeValue)
            ->where('is_active', true)
            ->whereDate('expires_at', '>=', today())
            ->first();

        if (! $promoCode) {
            return response()->json([
                'success' => false,
                'message' => 'This promo code is invalid or expired.',
            ], 422);
        }

        $this->cart->applyPromoCode($promoCode);

        return response()->json($this->promoCodeResponse($promoCode, 'Promo code applied.'));
    }

    /** Remove the applied promo code from the current cart. */
    public function removePromoCode(): JsonResponse
    {
        $this->cart->removePromoCode();

        return response()->json([
            'success' => true,
            'subtotal' => $this->moneyLabel($this->cart->subtotalCents()),
            'discount' => $this->moneyLabel(0),
            'total' => $this->moneyLabel($this->cart->subtotalCents()),
            'message' => 'Promo code removed.',
        ]);
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

    /** Return display-ready promo and cart totals for the cart AJAX response. */
    private function promoCodeResponse(PromoCode $promoCode, string $message): array
    {
        return [
            'success' => true,
            'code' => $promoCode->code,
            'discount_percent' => $promoCode->discount_percent,
            'subtotal' => $this->moneyLabel($this->cart->subtotalCents()),
            'discount' => $this->moneyLabel($this->cart->discountAmountCents($promoCode)),
            'total' => $this->moneyLabel($this->cart->totalCents($promoCode)),
            'message' => $message,
        ];
    }
}
