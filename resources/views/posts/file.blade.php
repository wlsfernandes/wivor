@extends('layouts.master')

@section('title')
    @lang('translation.Form_File_Upload')
@endsection

@section('content')
    @component('common-components.breadcrumb')
    @slot('pagetitle') Forms @endslot
    @slot('title') File Upload @endslot
    @endcomponent
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($post->file_url)
                        <a href="{{$post->file_url}}" target="blank"><img
                                src="{{ asset('/assets/images/brands/pdf-icon.png') }}" alt="JPG Icon"
                                style="width: 32px; height: 32px; vertical-align: middle;"> Click here to see the PDF File
                            uploaded</a>
                    @endif
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

                    <h5 class="card-title">
                        Upload PDF Files to be displayed AETH website
                    </h5>

                    <form method="POST" action="{{ route('post.uploadFile', $post->id) }}" accept-charset="UTF-8"
                        enctype="multipart/form-data" style="display:inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="language" value="{{$language}}" />
                        <!-- Image upload field -->
                        <div class="mb-3 row" style="margin-top:75px;">
                            <label for="image" class="col-md-2 col-form-label">Blog Image:</label>
                            <div class="col-md-10">
                                <input type="file" class="form-control" name="document" id="pdf" name="pdf"
                                    accept="application/pdf">
                                <small class="form-text text-muted">
                                    Maximum size: 5MB. Allowed format: PDF only.
                                </small>
                            </div>

                        </div>

                        <div class="d-flex flex-wrap gap-3">
                            <button type="submit" class="btn btn-secondary waves-effect">
                                <a href="{{ url('/post') }}" class="btn btn-secondary waves-effect">
                                    <i class="bx bx-arrow-back"></i> Go Back
                                </a>
                            </button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light"><i
                                    class="ui-plus"></i>Update </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
