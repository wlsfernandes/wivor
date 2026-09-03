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
                            <img class="card-img-top" style="aspect-ratio: 1 / 1; object-fit: cover;" src="{{ route('events.photos.image', ['event' => $event->slug, 'photo' => $photo]) }}" alt="{{ $photo->display_alt_text }}">
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
                    <div>
                        <p class="mb-0">{{ $photos->count() }} photo(s) selected</p>
                        <p class="h4 mb-0">Subtotal: ${{ number_format($subtotalCents / 100, 2) }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('cart.clear') }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-secondary" type="submit">Clear selection</button>
                        </form>
                        <form method="POST" action="{{ route('checkout.store') }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">Continue to Secure Checkout</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </main>
@endsection
