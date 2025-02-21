@extends('layouts.master')
@section('title')
    Users
@endsection
@section('css')
    <!-- DataTables -->
    <link href="{{ asset('/assets/libs/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @component('common-components.breadcrumb')
    @slot('pagetitle')
    Users
    @endslot
    @slot('title')
    @endslot
    @endcomponent


    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="my-0 text-primary"> <img src="{{  asset('assets/images/logo/wivor.png')}}"
                            style="width:32px;"></img> System Users
                    </h5>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> <!-- Success icon -->
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (request()->has('error'))
                        <div class="alert alert-danger">
                            {{ request()->query('error') }}
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

                    <div>

                        <a href="{{ url('users/create') }}">
                            <button type="button" class="btn btn-success waves-effect waves-light mb-3"><i
                                    class="fas fa-plus"></i> Add New</button> </a>
                    </div>

                    <h4 class="card-title">Blog - users</h4>

                    <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $user->name ?? '' }}</td>
                                    <td>{{ $user->email ?? '' }}</td>
                                    <td>
                                        <small>@foreach ($user->roles as $role)
                                            {{ $role->name }}{{ !$loop->last ? '<br/> ' : '' }}
                                        @endforeach
                                        </small>
                                    </td>
                                    <td>
                                        <a href="{{ url('/users/' . $user->id . '/edit') }}" class="px-3 text-primary"><i
                                                class="uil uil-pen font-size-18"></i></a>

                                        <a href="javascript:void(0);" class="px-3 text-danger"
                                            onclick="event.preventDefault(); if(confirm('Confirm delete?')) { document.getElementById('delete-form-{{ $user->id }}').submit(); }">
                                            <i class="uil uil-trash-alt font-size-18"></i>
                                        </a>


                                        <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}"
                                            method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
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
