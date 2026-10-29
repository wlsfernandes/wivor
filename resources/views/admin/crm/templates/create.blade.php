@extends('layouts.master')
@section('title', 'New Email Template')
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.templates.index') }}">← Email templates</a>
        <h1 class="h3 mt-2 mb-3">New email template</h1>
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('admin.crm.templates.store') }}">
                @csrf
                @include('admin.crm.templates._form')
                <button type="submit" class="btn btn-primary">Save template</button>
            </form>
        </div></div>
    </div>
@endsection
@section('script-bottom')
    @include('admin.crm._editor-script')
@endsection
