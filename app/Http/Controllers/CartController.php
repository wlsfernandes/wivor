<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Photo;
use App\Models\PromoCode;
use App\Services\CartService;
use App\Services\PhotoPricingService;
use App\Services\PriceQuote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Handles the guest photo-selection cart used before Stripe checkout. */
class CartController extends Controller
{
    private const PACKET_BEATS_PROMO_MESSAGE = 'Your bundle discount is larger than this promo code, so the bundle discount applies.';

    public function __construct(private readonly CartService $cart, private readonly PhotoPricingService $pricing)
    {
    }

    /** Display the current selection and subtotal. */
    public function index(Request $request): View
    {
        $checkoutToken = (string) Str::uuid();
        $request->session()->put('wivor_checkout_token', $checkoutToken);
        $promoCode = $this->cart->promoCode();
        $event = $this->cart->event();
        $quote = $this->cart->quote($promoCode);

        return view('frontend.v1.cart.show', [
            'event' => $event,
            'photos' => $this->cart->photos(),
            'photoCountLabel' => $this->photoCountLabel($this->cart->count()),
            'subtotalLabel' => $this->moneyLabel($quote?->subtotalCents() ?? 0),
            'promoCode' => $promoCode,
            'discountRowLabel' => $quote ? $this->discountRowLabel($quote, $promoCode) : null,
            'promoStatusLabel' => $this->promoStatusLabel($quote, $promoCode),
            'discountLabel' => $this->moneyLabel($quote?->discountCents() ?? 0),
            'totalLabel' => $this->moneyLabel($quote?->totalCents() ?? 0),
            'packetHintLabel' => $event ? $this->packetHintLabel($event) : null,
            'checkoutToken' => $checkoutToken,
            'layout' => 'frontend.v1.layouts.app',
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

        return response()->json($this->promoCodeResponse($promoCode));
    }

    /** Remove the applied promo code from the current cart. */
    public function removePromoCode(): JsonResponse
    {
        $this->cart->removePromoCode();
        $quote = $this->cart->quote();

        return response()->json([
            'success' => true,
            'subtotal' => $this->moneyLabel($quote?->subtotalCents() ?? 0),
            'discount_label' => $quote ? $this->discountRowLabel($quote, null) : null,
            'discount' => $this->moneyLabel($quote?->discountCents() ?? 0),
            'total' => $this->moneyLabel($quote?->totalCents() ?? 0),
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
    private function promoCodeResponse(PromoCode $promoCode): array
    {
        $quote = $this->cart->quote($promoCode);

        return [
            'success' => true,
            'code' => $promoCode->code,
            'discount_percent' => $promoCode->discount_percent,
            'subtotal' => $this->moneyLabel($quote?->subtotalCents() ?? 0),
            'discount_label' => $quote ? $this->discountRowLabel($quote, $promoCode) : null,
            'discount' => $this->moneyLabel($quote?->discountCents() ?? 0),
            'total' => $this->moneyLabel($quote?->totalCents() ?? 0),
            'message' => $this->promoStatusLabel($quote, $promoCode),
        ];
    }

    /** Return the cart row label for the applied discount, or null when no discount applies. */
    private function discountRowLabel(PriceQuote $quote, ?PromoCode $promoCode): ?string
    {
        return match ($quote->discountSource) {
            Order::DISCOUNT_PACKET => 'Bundle discount ('.PhotoPricingService::percentageLabel($quote->discountPercentage).')',
            Order::DISCOUNT_PROMO => 'Promo '.$promoCode?->code,
            default => null,
        };
    }

    /** Return the status line shown under the promo code field, or null when no promo code is applied. */
    private function promoStatusLabel(?PriceQuote $quote, ?PromoCode $promoCode): ?string
    {
        if (! $promoCode) {
            return null;
        }

        return $quote?->discountSource === Order::DISCOUNT_PACKET
            ? self::PACKET_BEATS_PROMO_MESSAGE
            : $promoCode->code.' applied - '.$promoCode->discount_percent.'% off';
    }

    /** Return the prompt that tells the buyer how many more photos unlock the packet discount, or null. */
    private function packetHintLabel(Event $event): ?string
    {
        $photosNeeded = $this->pricing->photosNeededForPacketDiscount($event, $this->cart->count());

        if ($photosNeeded === 0) {
            return null;
        }

        return 'Add '.$photosNeeded.' more '.($photosNeeded === 1 ? 'photo' : 'photos')
            .' to save '.PhotoPricingService::percentageLabel($event->effectivePacketDiscountPercentage()).'.';
    }
}
