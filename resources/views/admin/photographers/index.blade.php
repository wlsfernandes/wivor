@extends('layouts.master')

@section('title', 'Photographer Applications')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                        <div>
                            <h1 class="h4 mb-1">Photographer applications</h1>
                            <p class="text-muted mb-0">Review applications and manage photographer access.</p>
                        </div>
                        <form method="GET" action="{{ route('photographers.list') }}" class="d-flex gap-2">
                            <label class="visually-hidden" for="status">Account state</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All states</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary" type="submit">Filter</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Photographer</th>
                                    <th scope="col">Location</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Verified</th>
                                    <th scope="col">State</th>
                                    <th scope="col">Registered</th>
                                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($photographers as $photographer)
                                    <tr>
                                        <td>{{ $photographer['name'] }}</td>
                                        <td>{{ $photographer['location'] }}</td>
                                        <td>{{ $photographer['email'] }}</td>
                                        <td>{{ $photographer['email_verified_label'] }}</td>
                                        <td>{{ $photographer['status_label'] }}</td>
                                        <td>{{ $photographer['registered_at_label'] }}</td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ $photographer['review_url'] }}">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No photographer applications match this filter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $photographers->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
