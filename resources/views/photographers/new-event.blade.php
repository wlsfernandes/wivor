@extends('layouts.app-sidebar')

@section('title', '#WiVor | Events')

@section('meta-description', 'This is a brief description of the blog page.')

@section('meta-keywords', 'blog, posts, news')

@section('content')
    <section id="event_create_section" class="contact-section sec-pad">
        <div class="auto-container">
            <div class="sec-title centred mb_55">
                <span class="sub-title calendar">@lang('messages.create_event')</span>
            </div>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 form-column">
                    <div class="form-inner">
                        <form method="POST" action="{{ route('eventCreatedByPhotographer') }}" enctype="multipart/form-data"
                            autocomplete="off">
                            @csrf
                            <div class="row clearfix">

                                <!-- Title -->
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                    <input type="text" name="title" class="form-control"
                                        placeholder="@lang('messages.title')" required>
                                </div>

                                <!-- Date of Event -->
                                <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                    <input type="date" name="date_of_event" class="form-control"
                                        placeholder="@lang('messages.date_of_event')" required>
                                    <small>Date of Event</small>
                                </div>

                                <!-- Image Upload -->
                                <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                    <input type="file" name="image_url" class="form-control" accept="image/*" required>
                                    <p class="form-text text-muted">@lang('messages.choose_image')</p>
                                </div>

                                <!-- Content -->
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                    <textarea name="content" class="form-control" rows="5"
                                        placeholder="@lang('messages.content')"></textarea>
                                </div>

                                <!-- Summary -->
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                    <textarea name="summary" class="form-control" rows="3"
                                        placeholder="@lang('messages.summary')"></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group text-center">
                                    <button class="theme-btn-one btn btn-primary" type="submit">
                                        <i class="bi bi-calendar-plus"></i>
                                        <span>@lang('messages.create_event')</span>
                                    </button>
                                </div>

                            </div> <!-- /row -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection