@php($cartCount = count((array) session('wivor_cart', [])))
<header class="wv-header">
    <div class="wv-container wv-header__bar">
        <a href="{{ route('welcome') }}" class="wv-header__logo">
            <img src="{{ asset('assets/images/logo/wivor.png') }}" alt="WiVor home">
        </a>

        <nav id="wv-primary-nav" class="wv-header__nav" aria-label="Main navigation">
            <ul class="wv-header__links">
                <li><a href="{{ route('how-it-works') }}" @class(['is-current' => request()->routeIs('how-it-works')])>How It Works</a></li>
                <li><a href="{{ route('athletes') }}" @class(['is-current' => request()->routeIs('athletes')])>Athletes</a></li>
                <li><a href="{{ route('photographers') }}" @class(['is-current' => request()->routeIs('photographers')])>Photographers</a></li>
                <li><a href="{{ route('organizers') }}" @class(['is-current' => request()->routeIs('organizers')])>Organizers</a></li>
                <li><a href="{{ route('faq') }}" @class(['is-current' => request()->routeIs('faq')])>FAQ</a></li>
            </ul>
            <ul class="wv-header__utility">
                <li>
                    <a href="{{ route('cart.show') }}"
                        aria-label="View cart with {{ $cartCount }} selected {{ $cartCount === 1 ? 'photo' : 'photos' }}">
                        <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                        @if ($cartCount > 0)
                            <span class="wv-header__cart-count" aria-hidden="true">{{ $cartCount }}</span>
                        @endif
                    </a>
                </li>
                <li><a href="{{ route('login') }}">Log in</a></li>
            </ul>
        </nav>

        <a href="{{ route('athletes') }}" class="wv-btn wv-btn--primary wv-header__cta">Find My Photos</a>

        <button type="button" class="wv-header__toggle" aria-controls="wv-primary-nav" aria-expanded="false"
            aria-label="Open menu">
            <span class="wv-header__toggle-bar"></span>
            <span class="wv-header__toggle-bar"></span>
            <span class="wv-header__toggle-bar"></span>
        </button>
    </div>
</header>

<div class="boxed_wrapper">
    @if (session()->has('success'))
        <div class="alert alert-success mb-0" role="alert">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-0" role="alert">
            @if (is_array(session('error')))
                {{ implode(', ', session('error')) }}
            @else
                {{ session('error') }}
            @endif
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-0" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
