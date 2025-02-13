@extends('layouts.master')
@section('title')
    Blog
@endsection
@section('css')
    <!-- DataTables -->
    <link href="{{ asset('/assets/libs/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @component('common-components.breadcrumb')
    @slot('pagetitle')
    Blog
    @endslot
    @slot('title')
    @endslot
    @endcomponent


    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="my-0 text-primary"> <i class="text-warning far fa-calendar font-size-18"></i><b> Events </b>
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

                        <a href="{{ url('posts/create') }}">
                            <button type="button" class="btn btn-success waves-effect waves-light mb-3"><i
                                    class="fas fa-plus"></i> Add New</button> </a>
                    </div>

                    <h4 class="card-title">Blog - Posts</h4>

                    <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Title En</th>
                                <th class="text-center">Upload JPG</th>
                                <th class="text-center">English PDF</th>
                                <th class="text-center">Spanish PDF</th>
                                <th class="text-center">Publish</th>
                                <th class="text-center">Preview</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($posts as $post)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if ($post->postType && $post->postType->name == 'blog')
                                            <div class="badge bg-pill bg-primary-subtle text-primary font-size-12">Blog</div>
                                        @elseif ($post->postType && $post->postType->name == 'event')
                                            <div class="badge bg-pill bg-warning-subtle text-warning font-size-12">Event</div>
                                        @else ($post->postType && $post->postType->name == 'simple page')
                                            <div class="badge bg-pill bg-info-subtle text-info font-size-12">Simple Page</div>
                                        @endif
                                    </td>
                                    <td><i>{{ substr($post->title_en ?? '', 0, 25) }}...</i></td>
                                    <td class="text-center">
                                        <a href="{{ url('/post/' . $post->id . '/teaser') }}" class="px-3 text-primary">
                                            @if($post->image_url)
                                                <i class="text-primary uil-image-upload font-size-20"></i>
                                            @else
                                                <i class="text-muted uil-image-upload font-size-20"></i>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{  route('post.file', ['id' => $post->id, 'language' => 'en']) }}"
                                            class="px-3 text-primary">
                                            @if($post->file_url)
                                                <i class="text-primary uil-file font-size-20"></i>
                                            @else
                                                <i class="text-muted uil-file font-size-20"></i>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('post.file', ['id' => $post->id, 'language' => 'es']) }} "
                                            class="px-3 text-primary">
                                            @if($post->file_url_es)
                                                <i class="text-primary uil-file font-size-20"></i>
                                            @else
                                                <i class="text-muted uil-file font-size-20"></i>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="text-center">

                                        @if($post->published)
                                            <a href="javascript:void(0);" class="px-3 text-primary"
                                                onclick="event.preventDefault(); if(confirm('Confirm publish?')) { document.getElementById('publish-form-{{ $post->id }}').submit(); }">
                                                @if ($post->postType && $post->postType->name == 'blog')
                                                    <i class="text-primary uil-blogger-alt font-size-20"></i>
                                                @elseif($post->postType && $post->postType->name == 'event')
                                                    <i class="text-warning far fa-calendar font-size-18"></i>
                                                @else($post->postType && $post->postType->name == 'simple page')
                                                    <i class="text-info dripicons-browser font-size-18"></i>
                                                @endif
                                            </a>

                                            <form id="publish-form-{{ $post->id }}"
                                                action="{{ url('/post/' . $post->id . '/publish') }}" method="POST"
                                                style="display: none;">
                                                @method('POST')
                                                @csrf
                                            </form>
                                        @else
                                            <a href="javascript:void(0);" class="px-3 text-primary"
                                                onclick="event.preventDefault(); if(confirm('Confirm publish?')) { document.getElementById('publish-form-{{ $post->id }}').submit(); }">
                                                @if ($post->postType && $post->postType->name == 'blog')
                                                    <i class="text-muted uil-blogger-alt font-size-20"></i>
                                                @elseif($post->postType && $post->postType->name == 'event')
                                                    <i class="text-muted far fa-calendar font-size-18"></i>
                                                @else($post->postType && $post->postType->name == 'simple page')
                                                    <i class="text-muted dripicons-browser font-size-18"></i>
                                                @endif
                                            </a>

                                            <form id="publish-form-{{ $post->id }}"
                                                action="{{ url('/post/' . $post->id . '/publish') }}" method="POST"
                                                style="display: none;">
                                                @method('POST')
                                                @csrf
                                            </form>
                                        @endif
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        @if($post->published)
                                            <a href="{{ config('app.link_website') . '/posts/' . $post->slug }}"
                                                class="px-3 text-primary" target="blank">
                                                <i class="text-primary fas fa-eye font-size-15"></i> </a>
                                        @else
                                            <i class="text-muted fas fa-eye-slash font-size-15"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('/post/' . $post->id . '/edit') }}" class="px-3 text-primary"><i
                                                class="uil uil-pen font-size-18"></i></a>

                                        <a href="javascript:void(0);" class="px-3 text-danger"
                                            onclick="event.preventDefault(); if(confirm('Confirm delete?')) { document.getElementById('delete-form-{{ $post->id }}').submit(); }">
                                            <i class="uil uil-trash-alt font-size-18"></i>
                                        </a>

                                        <form id="delete-form-{{ $post->id }}" action="{{ url('/post/' . $post->id) }}"
                                            method="POST" style="display: none;">
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
