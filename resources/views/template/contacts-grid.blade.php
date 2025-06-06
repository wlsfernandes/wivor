@extends('layouts.master')
@section('title')
    @lang('translation.User_Grid')
@endsection

@section('content')
    @component('common-components.breadcrumb')
        @slot('pagetitle') Contacts @endslot
        @slot('title') User Grid @endslot
    @endcomponent

    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card text-center">
                <div class="card-body">
                    <div class="dropdown float-end">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true">
                            <i class="uil uil-ellipsis-h"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Edit</a>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Remove</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="mb-4">
                        <img src="{{ asset('/assets/images/users/avatar-2.jpg') }}" alt=""
                            class="avatar-lg rounded-circle img-thumbnail">
                    </div>
                    <h5 class="font-size-16 mb-1"><a href="#" class="text-reset">Simon Ryles</a></h5>
                    <p class="text-muted mb-2">Full Stack Developer</p>

                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-light text-truncate"><i class="uil uil-user me-1"></i>
                        Profile</button>
                    <button type="button" class="btn btn-outline-light text-truncate"><i
                            class="uil uil-envelope-alt me-1"></i> Message</button>

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card text-center">
                <div class="card-body">
                    <div class="dropdown float-end">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true">
                            <i class="uil uil-ellipsis-h"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Edit</a>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Remove</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="mb-4">
                        <img src="{{ asset('/assets/images/users/avatar-3.jpg') }}" alt=""
                            class="avatar-lg rounded-circle img-thumbnail">
                    </div>
                    <h5 class="font-size-16 mb-1"><a href="#" class="text-reset">Marion Walker</a></h5>
                    <p class="text-muted mb-2">Frontend Developer</p>

                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-light text-truncate"><i class="uil uil-user me-1"></i>
                        Profile</button>
                    <button type="button" class="btn btn-outline-light text-truncate"><i
                            class="uil uil-envelope-alt me-1"></i> Message</button>

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card text-center">
                <div class="card-body">
                    <div class="dropdown float-end">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true">
                            <i class="uil uil-ellipsis-h"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Edit</a>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Remove</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-primary-subtle rounded-circle text-primary">
                            <i class="mdi mdi-account-circle display-4 m-0 text-primary"></i>
                        </div>
                    </div>
                    <h5 class="font-size-16 mb-1"><a href="#" class="text-reset">Frederick White</a></h5>
                    <p class="text-muted mb-2">UI/UX Designer</p>

                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-light text-truncate"><i class="uil uil-user me-1"></i>
                        Profile</button>
                    <button type="button" class="btn btn-outline-light text-truncate"><i
                            class="uil uil-envelope-alt me-1"></i> Message</button>

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card text-center">
                <div class="card-body">
                    <div class="dropdown float-end">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true">
                            <i class="uil uil-ellipsis-h"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Edit</a>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Remove</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="mb-4">
                        <img src="{{ asset('/assets/images/users/avatar-4.jpg') }}" alt=""
                            class="avatar-lg rounded-circle img-thumbnail">
                    </div>
                    <h5 class="font-size-16 mb-1"><a href="#" class="text-reset">Shanon Marvin</a></h5>
                    <p class="text-muted mb-2">Backend Developer</p>

                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-light text-truncate"><i class="uil uil-user me-1"></i>
                        Profile</button>
                    <button type="button" class="btn btn-outline-light text-truncate"><i
                            class="uil uil-envelope-alt me-1"></i> Message</button>

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card text-center">
                <div class="card-body">
                    <div class="dropdown float-end">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true">
                            <i class="uil uil-ellipsis-h"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Edit</a>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Remove</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-primary-subtle rounded-circle text-primary">
                            <i class="mdi mdi-account-circle display-4 m-0 text-primary"></i>
                        </div>
                    </div>
                    <h5 class="font-size-16 mb-1"><a href="#" class="text-reset">Mark Jones</a></h5>
                    <p class="text-muted mb-2">Frontend Developer</p>

                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-light text-truncate"><i class="uil uil-user me-1"></i>
                        Profile</button>
                    <button type="button" class="btn btn-outline-light text-truncate"><i
                            class="uil uil-envelope-alt me-1"></i> Message</button>

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card text-center">
                <div class="card-body">
                    <div class="dropdown float-end">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true">
                            <i class="uil uil-ellipsis-h"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Edit</a>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Remove</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="mb-4">
                        <img src="{{ asset('/assets/images/users/avatar-5.jpg') }}" alt=""
                            class="avatar-lg rounded-circle img-thumbnail">
                    </div>
                    <h5 class="font-size-16 mb-1"><a href="#" class="text-reset">Janice Morgan</a></h5>
                    <p class="text-muted mb-2">Backend Developer</p>

                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-light text-truncate"><i class="uil uil-user me-1"></i>
                        Profile</button>
                    <button type="button" class="btn btn-outline-light text-truncate"><i
                            class="uil uil-envelope-alt me-1"></i> Message</button>

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card text-center">
                <div class="card-body">
                    <div class="dropdown float-end">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true">
                            <i class="uil uil-ellipsis-h"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Edit</a>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Remove</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="mb-4">
                        <img src="{{ asset('/assets/images/users/avatar-7.jpg') }}" alt=""
                            class="avatar-lg rounded-circle img-thumbnail">
                    </div>
                    <h5 class="font-size-16 mb-1"><a href="#" class="text-reset">Patrick Petty</a></h5>
                    <p class="text-muted mb-2">UI/UX Designer</p>

                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-light text-truncate"><i class="uil uil-user me-1"></i>
                        Profile</button>
                    <button type="button" class="btn btn-outline-light text-truncate"><i
                            class="uil uil-envelope-alt me-1"></i> Message</button>

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card text-center">
                <div class="card-body">
                    <div class="dropdown float-end">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true">
                            <i class="uil uil-ellipsis-h"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Edit</a>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Remove</a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="mb-4">
                        <img src="{{ asset('/assets/images/users/avatar-8.jpg') }}" alt=""
                            class="avatar-lg rounded-circle img-thumbnail">
                    </div>
                    <h5 class="font-size-16 mb-1"><a href="#" class="text-reset">Marilyn Horton</a></h5>
                    <p class="text-muted mb-2">Frontend Developer</p>

                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-light text-truncate"><i class="uil uil-user me-1"></i>
                        Profile</button>
                    <button type="button" class="btn btn-outline-light text-truncate"><i
                            class="uil uil-envelope-alt me-1"></i> Message</button>

                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row mt-3">
        <div class="col-xl-12">
            <div class="text-center my-3">
                <a href="javascript:void(0);" class="text-primary"><i
                        class="mdi mdi-loading mdi-spin font-size-20 align-middle me-2"></i> Load more </a>
            </div>
        </div>
    </div>
    <!-- end row -->

@endsection
