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

                        <a href="{{ route('events.create') }}">
                            <button type="button" class="btn btn-success waves-effect waves-light mb-3"><i
                                    class="fas fa-plus"></i> Add New</button> </a>
                    </div>

                    <h4 class="card-title">Blog - Posts</h4>

                    <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title En</th>
                                <th class="text-center">Upload JPG</th>
                                <th class="text-center">Publish</th>
                                <th class="text-center">Preview</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><i>{{ substr($event->title ?? '', 0, 25) }}...</i></td>
                                    <td class="text-center">
                                        <a href="{{ url('/events/' . $event->id . '/teaser') }}" class="px-3 text-primary">
                                            @if($event->image_url)
                                                <i class="text-primary uil-image-upload font-size-20"></i>
                                            @else
                                                <i class="text-muted uil-image-upload font-size-20"></i>
                                            @endif
                                        </a>
                                    </td>


                                    <td class="text-center">

                                        @if($event->published)
                                            <a href="javascript:void(0);" class="px-3 text-primary"
                                                onclick="event.preventDefault(); if(confirm('Confirm publish?')) { document.getElementById('publish-form-{{ $event->id }}').submit(); }">
                                                @if ($event->postType && $event->postType->name == 'blog')
                                                    <i class="text-primary uil-blogger-alt font-size-20"></i>
                                                @elseif($event->postType && $event->postType->name == 'event')
                                                    <i class="text-warning far fa-calendar font-size-18"></i>
                                                @else($event->postType && $event->postType->name == 'simple page')
                                                    <i class="text-info dripicons-browser font-size-18"></i>
                                                @endif
                                            </a>

                                            <form id="publish-form-{{ $event->id }}"
                                                action="{{ url('/events/' . $event->id . '/publish') }}" method="POST"
                                                style="display: none;">
                                                @method('POST')
                                                @csrf
                                            </form>
                                        @else
                                            <a href="javascript:void(0);" class="px-3 text-primary"
                                                onclick="event.preventDefault(); if(confirm('Confirm publish?')) { document.getElementById('publish-form-{{ $event->id }}').submit(); }">
                                                    <i class="text-muted far fa-calendar font-size-18"></i>
                                            </a>

                                            <form id="publish-form-{{ $event->id }}"
                                                action="{{ url('/events/' . $event->id . '/publish') }}" method="POST"
                                                style="display: none;">
                                                @method('POST')
                                                @csrf
                                            </form>
                                        @endif
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        @if($event->published)
                                            <a href="{{ config('app.link_website') . '/events/' . $event->slug }}"
                                                class="px-3 text-primary" target="blank">
                                                <i class="text-primary fas fa-eye font-size-15"></i> </a>
                                        @else
                                            <i class="text-muted fas fa-eye-slash font-size-15"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('/events/' . $event->id . '/edit') }}" class="px-3 text-primary"><i
                                                class="uil uil-pen font-size-18"></i></a>

                                        <a href="javascript:void(0);" class="px-3 text-danger"
                                            onclick="event.preventDefault(); if(confirm('Confirm delete?')) { document.getElementById('delete-form-{{ $event->id }}').submit(); }">
                                            <i class="uil uil-trash-alt font-size-18"></i>
                                        </a>

                                        <form id="delete-form-{{ $event->id }}" action="{{ url('/events/' . $event->id) }}"
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
