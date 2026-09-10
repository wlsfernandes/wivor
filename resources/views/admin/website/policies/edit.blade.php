@extends('layouts.master')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card">
                <div class="card-body">
                    <h1 class="h4 mb-1">{{ $title }}</h1>
                    <p class="text-muted mb-4">Update the text shown on the public {{ $title }} page.</p>

                    @if (session('success'))
                        <div class="alert alert-success" role="status">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.website.policies.update', ['policy' => $policy]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="content">Policy text</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content"
                                name="content" rows="20" required>{{ old('content', $content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-primary" type="submit">Save {{ $title }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
