@extends('layouts.master')
@section('title')
    Blog - Posts
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
    Blog - Posts
    @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="d-flex justify-content-between" style="margin:15px">
                    <a href="{{ url('/events') }}" class="btn btn-secondary waves-effect">
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

                    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3 row">
                            <label for="title" class="col-md-2 col-form-label">Title:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" value="{{ old('title', $event->title ?? '') }}"
                                    id="title" name="title" required>
                                <p class="form-text text-muted">Enter the title of the post in English. Keep it short and
                                    descriptive.</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="date_of_publication" class="col-md-2 col-form-label">Date of Publication</label>
                            <div class="col-md-4">
                                <input type="date" class="form-control flatpickr" id="date_of_publication"
                                    name="date_of_publication"
                                    value="{{ old('date_of_publication', $event->date_of_publication ?? '') }}">
                                <p class="form-text text-muted">Choose the event date</p>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="content" class="col-md-2 col-form-label">Content:</label>
                            <div class="col-md-10">
                                <textarea class="form-control" id="content" name="content"
                                    required>{{ old('content', $event->content ?? '') }}</textarea>
                                <p class="form-text text-muted">Write the full content of the post in English. Use proper
                                    formatting and detail.</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="summary" class="col-md-2 col-form-label">Summary</label>
                            <div class="col-md-10">
                                <textarea class="form-control" id="summary"
                                    name="summary">{{ old('summary', $event->summary ?? '') }}</textarea>
                                <p class="form-text text-muted">Provide a brief summary of the post in English. This will be
                                    shown on previews.</p>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="image_url" class="col-md-2 col-form-label">Upload Image</label>
                            <div class="col-md-10">
                                <input type="file" class="form-control" id="image_url" name="image_url" accept="image/*">
                                <p class="form-text text-muted">Choose a feature image for the post.</p>
                            </div>
                        </div>


                        <div class="d-flex flex-wrap gap-3">
                            <a href="{{ url('/events') }}" class="btn btn-secondary waves-effect">
                                <i class="bx bx-arrow-back"></i> Go Back
                            </a>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                <i class="ui-plus"></i> Save
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->

@endsection