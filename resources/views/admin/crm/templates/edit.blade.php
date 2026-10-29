@extends('layouts.master')
@section('title', 'Edit Email Template')
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.templates.index') }}">← Email templates</a>
        <h1 class="h3 mt-2 mb-3">Edit email template</h1>
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('admin.crm.templates.update', $template) }}">
                @csrf
                @method('PUT')
                @include('admin.crm.templates._form')
                <button type="submit" class="btn btn-primary">Save template</button>
            </form>
        </div></div>
    </div>
@endsection
@section('script-bottom')
    @include('admin.crm._editor-script')
@endsection
