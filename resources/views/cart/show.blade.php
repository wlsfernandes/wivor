@extends($layout)

@section('title', 'Your Selected Photos | WivorPhotos')

@section('content')
    <main class="container py-5">
        <header class="mb-4">
            <h1>Your Selected Photos</h1>
            @if ($event)
                <p class="text-muted mb-0">{{ $event->title }} &middot; {{ $event->price_label }} per photo</p>
            @endif
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        @if ($photos->isEmpty())
            <p class="text-muted">You have not selected any photos yet.</p>
            <a class="btn btn-outline-secondary" href="{{ route('events.listEvents') }}">Browse events</a>
        @else
            <div class="row g-3">
                @foreach ($photos as $photo)
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <img class="card-img-top" style="aspect-ratio: 1 / 1; object-fit: cover;"
                                src="{{ route('events.photos.image', ['event' => $event->slug, 'photo' => $photo]) }}"
                                alt="{{ $photo->display_alt_text }}">
                            <div class="card-body p-2 text-center">
                                <form method="POST" action="{{ route('cart.items.destroy', ['photo' => $photo->uuid]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger w-100" type="submit">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card mt-4">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="flex-grow-1" style="max-width: 420px;">
                        <p class="mb-0">{{ $photoCountLabel }}</p>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between gap-3">
                                <span>Subtotal</span>
                                <span data-cart-subtotal>{{ $subtotalLabel }}</span>
                            </div>
                            <div class="d-flex justify-content-between gap-3 {{ $promoCode ? '' : 'd-none' }}" data-discount-row>
                                <span>Promo <span data-discount-code>{{ $promoCode?->code }}</span></span>
                                <span class="text-success">-<span data-cart-discount>{{ $discountLabel }}</span></span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3 fw-bold">
                                <span>Total</span>
                                <span data-cart-total>{{ $totalLabel }}</span>
                            </div>
                        </div>

                        <form class="mt-3" data-promo-form action="{{ route('cart.promo-code.store') }}" method="POST">
                            @csrf
                            <label for="promo-code" class="form-label">Promo code</label>
                            <div class="input-group">
                                <input class="form-control" id="promo-code" name="promo_code" type="text" maxlength="50"
                                    value="{{ $promoCode?->code }}">
                                <button class="btn btn-outline-primary" type="submit" data-promo-apply>Apply</button>
                            </div>
                        </form>
                        <div class="small mt-2 {{ $promoCode ? 'text-success' : '' }}" role="status" data-promo-message>
                            @if ($promoCode)
                                {{ $promoCode->code }} applied - {{ $promoCode->discount_percent }}% off
                            @endif
                        </div>
                        <button type="button" class="btn btn-link btn-sm px-0 {{ $promoCode ? '' : 'd-none' }}"
                            data-promo-remove data-url="{{ route('cart.promo-code.destroy') }}">Remove promo code</button>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('cart.clear') }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-secondary" type="submit">Clear selection</button>
                        </form>
                        <form method="POST" action="{{ route('checkout.store') }}" data-checkout-form>
                            @csrf
                            <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
                            <button class="btn btn-primary" type="submit" data-checkout-button>Continue to Secure Checkout</button>
                            <p class="small text-muted mt-2 mb-0">By continuing, you agree to the <a href="{{ route('terms') }}">Terms of Use</a>. See the <a href="{{ route('refund-policy') }}">Refund Policy</a>.</p>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </main>
@endsection

@section('scripts')
    <script>
        const promoForm = document.querySelector('[data-promo-form]');
        const promoMessage = document.querySelector('[data-promo-message]');
        const promoRemoveButton = document.querySelector('[data-promo-remove]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        function updateCartTotals(response) {
            document.querySelector('[data-cart-subtotal]').textContent = response.subtotal;
            document.querySelector('[data-cart-discount]').textContent = response.discount;
            document.querySelector('[data-cart-total]').textContent = response.total;
        }

        promoForm?.addEventListener('submit', async function (event) {
            event.preventDefault();
            const applyButton = this.querySelector('[data-promo-apply]');
            applyButton.disabled = true;
            applyButton.textContent = 'Applying...';

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new FormData(this),
                });
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'This promo code could not be applied.');
                }

                updateCartTotals(result);
                document.querySelector('[data-discount-code]').textContent = result.code;
                document.querySelector('[data-discount-row]').classList.remove('d-none');
                promoMessage.textContent = `${result.code} applied - ${result.discount_percent}% off`;
                promoMessage.className = 'small mt-2 text-success';
                promoRemoveButton.classList.remove('d-none');
            } catch (error) {
                promoMessage.textContent = error.message;
                promoMessage.className = 'small mt-2 text-danger';
            } finally {
                applyButton.disabled = false;
                applyButton.textContent = 'Apply';
            }
        });

        promoRemoveButton?.addEventListener('click', async function () {
            this.disabled = true;

            try {
                const response = await fetch(this.dataset.url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'The promo code could not be removed.');
                }

                updateCartTotals(result);
                document.querySelector('[data-discount-row]').classList.add('d-none');
                promoMessage.textContent = result.message;
                promoMessage.className = 'small mt-2 text-muted';
                promoForm.querySelector('[name="promo_code"]').value = '';
                this.classList.add('d-none');
            } catch (error) {
                promoMessage.textContent = error.message;
                promoMessage.className = 'small mt-2 text-danger';
            } finally {
                this.disabled = false;
            }
        });

        document.querySelector('[data-checkout-form]')?.addEventListener('submit', function () {
            const button = this.querySelector('[data-checkout-button]');
            if (button) {
                button.disabled = true;
                button.textContent = 'Opening secure checkout…';
            }
        });
    </script>
@endsection
