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
                    <a href="{{ url('/posts') }}" class="btn btn-secondary waves-effect">
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

                    <form action="{{ url('/post') }}" method="post">
                        @csrf
                        <div class="mb-3 row">
                            <label for="post_type" class="col-md-2 col-form-label">Post Type:</label>
                            <div class="col-md-10">
                                <select class="form-control" id="post_type" name="post_type" required>
                                    <option value="">Select a Post Type</option>
                                    @foreach($postTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('post_type', $post->post_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="form-text text-muted">Choose the type of post from the list.</p>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="title_en" class="col-md-2 col-form-label">Title English:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" value="{{ old('title_en', $post->title_en ?? '') }}"
                                    id="title_en" name="title_en" required>
                                <p class="form-text text-muted">Enter the title of the post in English. Keep it short and
                                    descriptive.</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="title_es" class="col-md-2 col-form-label">Title Spanish:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" value="{{ old('title_es', $post->title_es ?? '') }}"
                                    id="title_es" name="title_es" required>
                                <p class="form-text text-muted">Enter the title of the post in Spanish. Make it clear and
                                    concise.</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="title_pt" class="col-md-2 col-form-label">Title Portuguese:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" value="{{ old('title_pt', $post->title_pt ?? '') }}"
                                    id="title_pt" name="title_pt" required>
                                <p class="form-text text-muted">Enter the title of the post in Portuguese. Keep it easy to
                                    understand. You can use AI to translate</p>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="content_en" class="col-md-2 col-form-label">Content English:</label>
                            <div class="col-md-10">
                                <textarea class="form-control" id="content_en" name="content_en"
                                    required>{{ old('content_en', $post->content_en ?? '') }}</textarea>
                                <p class="form-text text-muted">Write the full content of the post in English. Use proper
                                    formatting and detail.</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="content_es" class="col-md-2 col-form-label">Content Spanish:</label>
                            <div class="col-md-10">
                                <textarea class="form-control" id="content_es" name="content_es"
                                    required>{{ old('content_es', $post->content_es ?? '') }}</textarea>
                                <p class="form-text text-muted">Write the full content of the post in Spanish. Be thorough
                                    and clear.</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="content_pt" class="col-md-2 col-form-label">Content Portuguese:</label>
                            <div class="col-md-10">
                                <textarea class="form-control" id="content_pt" name="content_pt"
                                    required>{{ old('content_pt', $post->content_pt ?? '') }}</textarea>
                                <p class="form-text text-muted">Write the full content of the post in Portuguese. Include
                                    all necessary details.</p>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="summary_en" class="col-md-2 col-form-label">Summary English:</label>
                            <div class="col-md-10">
                                <textarea class="form-control" id="summary_en"
                                    name="summary_en">{{ old('summary_en', $post->summary_en ?? '') }}</textarea>
                                <p class="form-text text-muted">Provide a brief summary of the post in English. This will be
                                    shown on previews.</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="summary_es" class="col-md-2 col-form-label">Summary Spanish:</label>
                            <div class="col-md-10">
                                <textarea class="form-control" id="summary_es"
                                    name="summary_es">{{ old('summary_es', $post->summary_es ?? '') }}</textarea>
                                <p class="form-text text-muted">Provide a brief summary of the post in Spanish. Keep it
                                    concise and informative.</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="summary_pt" class="col-md-2 col-form-label">Summary Portuguese:</label>
                            <div class="col-md-10">
                                <textarea class="form-control" id="summary_pt"
                                    name="summary_pt">{{ old('summary_pt', $post->summary_pt ?? '') }}</textarea>
                                <p class="form-text text-muted">Provide a brief summary of the post in Portuguese. Ensure
                                    clarity and brevity.</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="date" class="col-md-2 col-form-label">Date:</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" value="{{ old('date', $post->date ?? '') }}"
                                    id="date" name="date" required>
                                <p class="form-text text-muted">
                                    Describe the date as "Month Year". For example: <code>Jan 25</code>.
                                </p>
                            </div>
                        </div>


                        <div class="d-flex flex-wrap gap-3">
                            <button type="submit" class="btn btn-secondary waves-effect">
                                <a href="{{ url('/posts') }}" class="btn btn-secondary waves-effect">
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
