@extends('layouts.master')
@section('title')
    Blog - users
@endsection

@section('css')
    <!-- plugin css -->
    <link href="{{ asset('/assets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/libs/spectrum-colorpicker/spectrum-colorpicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/assets/libs/bootstrap-touchspin/bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('/assets/libs/datepicker/datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/libs/flatpickr/flatpickr.min.css') }}">
@endsection


@section('content')
    @component('common-components.breadcrumb')
    @slot('pagetitle')
    AETH
    @endslot
    @slot('title')
    Blog - users
    @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="d-flex justify-content-between" style="margin:15px">
                    <a href="{{ url('/users') }}" class="btn btn-secondary waves-effect">
                        <i class="bx bx-arrow-back"></i> Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
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

                    <form action="{{ url('/users') }}" method="POST">
                        @csrf
                        <div class="mb-3 row">
                            <label for="name" class="col-md-2 col-form-label">Name:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" value="{{ old('name', $user->name ?? '') }}"
                                    id="name" name="name" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="email" class="col-md-2 col-form-label">Email:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="email" value="{{ old('email', $user->email ?? '') }}"
                                    id="email" name="email" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="role" class="col-md-2 col-form-label">Role:</label>
                            <div class="col-md-10">
                                <select class="form-control" id="role" name="role_ids" required>
                                    <option value="">Select a Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ (isset($user) && $user->roles->contains($role->id)) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                            <button type="submit" class="btn btn-secondary waves-effect">
                                <a href="{{ url('/users') }}" class="btn btn-secondary waves-effect">
                                    <i class="bx bx-arrow-back"></i> Go Back
                                </a>
                            </button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light"><i
                                    class="ui-plus"></i>Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->

@endsection
