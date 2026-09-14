@extends('layouts.master')
@section('title', 'Edit Promo Code')
@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-3">Edit Promo Code</h1>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.promo-codes.update', $promoCode) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.promo-codes._form')
                </form>
            </div>
        </div>
    </div>
@endsection