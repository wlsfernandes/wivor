@extends('layouts.master')
@section('title', 'Promo Codes')
@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h3 mb-0">Promo Codes</h1>
            <a href="{{ route('admin.promo-codes.create') }}" class="btn btn-success waves-effect waves-light">
                <i class="fas fa-plus" aria-hidden="true"></i> Add Promo Code
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Expiration Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($promoCodes as $promoCode)
                            <tr>
                                <td>{{ $promoCode['code'] }}</td>
                                <td>{{ $promoCode['discount'] }}</td>
                                <td>{{ $promoCode['expiration_date'] }}</td>
                                <td>
                                    <span class="badge {{ $promoCode['is_active'] ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $promoCode['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('admin.promo-codes.edit', $promoCode['id']) }}"
                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="POST"
                                            action="{{ route('admin.promo-codes.destroy', $promoCode['id']) }}"
                                            onsubmit="return confirm('Delete this promo code?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted text-center py-4">No promo codes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $promoCodes->links() }}</div>
    </div>
@endsection