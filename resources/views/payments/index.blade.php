@extends('layouts.master')
@section('title')
AMID
@endsection
@section('css')
<!-- DataTables -->
<link href="{{ asset('/assets/libs/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
@component('common-components.breadcrumb')
@slot('pagetitle')
@lang('app.courses')
@endslot
@slot('title')
@endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <div class="card border border-primary">
            <div class="card-header bg-transparent border-primary">
                <h5 class="my-0 text-primary"><i class="uil-money-bill"></i> Payments</b></h5>
            </div>
            <div class="card-body">
                @if (session()->has('success'))
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i> <!-- Success icon -->
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li><i class="bx bx-error"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h4 class="card-title">Payments</h4>
                <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap"
                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Currency</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $payment->student->user->email ?? ''}}</td>
                                <td>{{ $payment->course->title ?? ''}}</td>
                                <td>{{ $payment->transaction_id ?? ''}}</td>
                                <td>{{ $payment->status ?? ''}}</td>
                                <td>{{ $payment->amount ?? ''}}</td>
                                <td>{{ $payment->currency ?? ''}}</td>
                                <td>{{$payment->created_at ?? ''}}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>
        </div>
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection
@section('script')
<script src="{{ asset('/assets/libs/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('/assets/libs/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('/assets/libs/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('/assets/js/pages/datatables.init.js') }}"></script>

@endsection
