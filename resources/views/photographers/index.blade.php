@extends('layouts.master')
@section('title')
    Photographers
@endsection
@section('css')
    <!-- DataTables -->
    <link href="{{ asset('/assets/libs/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @component('common-components.breadcrumb')
    @slot('pagetitle')
    Photographers
    @endslot
    @slot('title')
    @endslot
    @endcomponent


    <div class="row">
        <div class="col-lg-12">
            <div class="card border border-primary">
                <div class="card-header bg-transparent border-primary">
                    <h5 class="my-0 text-primary"><i class="fa fa-camera"></i> WiVor Photographers</b></h5>
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

                    <h4 class="card-title">Photographers</h4>
                    <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>State</th>
                                <th>Zipcode</th>
                                <th>Profile Url</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($photographers as $photographer)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $photographer->first_name ? $photographer->first_name : '' }}</td>
                                    <td>{{ $photographer->last_name ?? '' }}</td>
                                    <td>{{ $photographer->state ?? '' }}</td>
                                    <td>{{ $photographer->zipcode ?? '' }}</td>
                                    <td><small>{{ $photographer->profile_url ?? '' }}</small></td>

                                    <td>
                                        <a href="{{ url('/photographers/' . $photographer->id) }}" class="px-3 text-primary"><i
                                                class="fas fa-eye"></i></a>
                                        <a href="{{ url('/photographers/' . $photographer->id . '/edit') }}"
                                            class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>

                                        <a href="javascript:void(0);" class="px-3 text-danger"
                                            onclick="event.preventDefault(); if(confirm('Confirm delete?')) { document.getElementById('delete-form-{{ $photographer->id }}').submit(); }">
                                            <i class="uil uil-trash-alt font-size-18"></i>
                                        </a>

                                        <form id="delete-form-{{ $photographer->id }}"
                                            action="{{ url('/photographers/' . $photographer->id) }}" method="POST"
                                            style="display: none;">
                                            @method('DELETE')
                                            @csrf
                                        </form>
                                    </td>
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
