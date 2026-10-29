@extends('layouts.master')
@section('title', 'CRM')
@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-3">CRM</h1>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Unique contacts</p>
                        <h2 class="h4 mb-0">{{ number_format($summary['contacts']) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Events with participants</p>
                        <h2 class="h4 mb-0">{{ number_format($summary['linkedEvents']) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">CSV imports</p>
                        <h2 class="h4 mb-0">{{ number_format($summary['imports']) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
