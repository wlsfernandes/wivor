@extends('layouts.master')
@section('title', 'Edit Email Campaign')
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.campaigns.show', $campaign) }}">← {{ $campaign->title }}</a>
        <h1 class="h3 mt-2 mb-3">Edit campaign</h1>
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('admin.crm.campaigns.update', $campaign) }}">
                @csrf
                @method('PUT')
                @include('admin.crm.campaigns._form')
                <button type="submit" class="btn btn-primary">Save draft</button>
            </form>
        </div></div>
    </div>
@endsection
@section('script-bottom')
    @include('admin.crm._editor-script')
@endsection
