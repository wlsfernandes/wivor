@extends('layouts.master')
@section('title')
@lang('translation.File_Manager')
@endsection

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" /> @endsection

@section('content')
@component('common-components.breadcrumb')
@slot('pagetitle') Apps @endslot
@slot('title') File Manager @endslot
@endcomponent

<div class="d-xl-flex">
    <div class="w-100">
        <div class="d-xl-flex">
            <div class="card filemanager-sidebar me-md-3">
                <div class="card-body">
                    <div class="d-flex flex-column h-100">
                        <div class="mb-4">
                            <div class="mb-3">
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-plus me-1"></i> Create New
                                    </button>
                                    <div class="dropdown-menu">
                                      <a class="dropdown-item" href="#"><i class="bx bx-folder me-1"></i> Folder</a>
                                      <a class="dropdown-item" href="#"><i class="bx bx-file me-1"></i> File</a>
                                    </div>
                                </div>
                            </div>
                            <ul class="list-unstyled categories-list">
                                <li>
                                    <div class="custom-accordion">
                                        <a class="text-body fw-medium py-1 d-flex align-items-center" data-bs-toggle="collapse" href="#categories-collapse" role="button" aria-expanded="false" aria-controls="categories-collapse">
                                            <i class="mdi mdi-folder font-size-20 text-warning me-2"></i> My Files <i class="mdi mdi-chevron-up accor-down-icon ms-auto"></i>
                                        </a>
                                        <div class="collapse show" id="categories-collapse">
                                            <div class="card border-0 shadow-none ps-2 mb-0">
                                                <ul class="list-unstyled mb-0">
                                                    <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Analytics</span></a></li>
                                                    <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Design</span></a></li>
                                                    <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Development</span> <i class="mdi mdi-pin ms-auto"></i></a></li>
                                                    <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Project A</span></a></li>
                                                    <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Admin</span> <i class="mdi mdi-pin ms-auto"></i></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                        <i class="mdi mdi-google-drive font-size-20 text-muted me-2"></i> <span class="me-auto">Google Drive</span> 
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                        <i class="mdi mdi-dropbox font-size-20 me-2 text-primary"></i> <span class="me-auto">Dropbox</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                        <i class="mdi mdi-share-variant font-size-20 me-2"></i> <span class="me-auto">Shared</span> <i class="mdi mdi-circle-medium text-danger ms-2"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                        <i class="mdi mdi-star-outline text-muted font-size-20 me-2"></i> <span class="me-auto">Starred</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                        <i class="mdi mdi-trash-can text-danger font-size-20 me-2"></i> <span class="me-auto">Trash</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                        <i class="mdi mdi-cog text-muted font-size-20 me-2"></i> <span class="me-auto">Setting</span><span class="badge bg-success rounded-pill ms-2">01</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-4 pt-3 mt-auto text-center">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <div class="px-4">
                                        <img src="{{ asset('assets/images/upgrade-img.png') }}" class="img-fluid" alt="">
                                    </div>
                                    <h5 class="mt-4">Upgrade Features</h5>
                                    <p class="pt-1">4 integrations, 30 team members, advanced features </p>
                                    <div class="text-center pt-2">
                                        <button type="button" class="btn btn-primary w-100">Upgrade <i class="mdi mdi-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- filemanager-leftsidebar -->

            <div class="w-100">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-size-16 me-3 mb-0">My Files</h5>

                        <div class="row mt-4">
                            <div class="col-xl-4 col-sm-6">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="">
                                            <div class="dropdown float-end">
                                                <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                    <i class="bx bx-dots-vertical-rounded font-size-20"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="#">Edit</a>
                                                    <a class="dropdown-item" href="#">Action</a>
                                                    <a class="dropdown-item" href="#">Remove</a>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center overflow-hidden">

                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar align-self-center">
                                                        <div class="avatar-title rounded bg-primary-subtle text-primary font-size-24">
                                                            <i class="mdi mdi-google-drive"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                               
                                                <div class="flex-grow-1">
                                                    <h5 class="font-size-15 mb-1 text-truncate">Google Drive</h5>
                                                    <a href="" class="font-size-14 text-muted text-truncate"><u>View Folder</u></a>
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-1">
                                                <div class="d-flex justify-content-between">
                                                    <p class="text-muted font-size-13 mb-1">15GB</p>
                                                    <p class="text-muted font-size-13 mb-1 text-truncate">25GB used</p>
                                                </div>
                                                <div class="progress animated-progess custom-progress">
                                                    <div class="progress-bar bg-gradient bg-primary" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="90">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-xl-4 col-sm-6">
                                <div class="card">
                                    <div class="card-body p-3">

                                        <div class="">
                                            <div class="dropdown float-end">
                                                <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                    <i class="bx bx-dots-vertical-rounded font-size-20"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="#">Edit</a>
                                                    <a class="dropdown-item" href="#">Action</a>
                                                    <a class="dropdown-item" href="#">Remove</a>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center overflow-hidden">

                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar align-self-center">
                                                        <div class="avatar-title rounded bg-info-subtle text-info font-size-24">
                                                            <i class="mdi mdi-dropbox"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                               
                                                <div class="flex-grow-1">
                                                    <h5 class="font-size-15 mb-1 text-truncate">Dropbox</h5>
                                                    <a href="" class="font-size-14 text-muted text-truncate"><u>View Folder</u></a>
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-1">
                                                <div class="d-flex justify-content-between">
                                                    <p class="text-muted font-size-13 mb-1">20GB</p>
                                                    <p class="text-muted font-size-13 mb-1 text-truncate">50GB used</p>
                                                </div>
                                                <div class="progress animated-progess custom-progress">
                                                    <div class="progress-bar bg-gradient bg-primary" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="90">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-xl-4 col-sm-6">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="">
                                            <div class="dropdown float-end">
                                                <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                    <i class="bx bx-dots-vertical-rounded font-size-20"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="#">Edit</a>
                                                    <a class="dropdown-item" href="#">Action</a>
                                                    <a class="dropdown-item" href="#">Remove</a>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center overflow-hidden">

                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar align-self-center me-3">
                                                        <div class="avatar-title rounded bg-primary-subtle text-primary font-size-24">
                                                            <i class="mdi mdi-apple-icloud"></i>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex-grow-1">
                                                    <h5 class="font-size-15 mb-1 text-truncate">One Drive</h5>
                                                    <a href="" class="font-size-14 text-muted text-truncate"><u>View Folder</u></a>
                                                </div>
                                            
                                            </div>
                                            <div class="mt-3 pt-1">
                                                <div class="d-flex justify-content-between">
                                                    <p class="text-muted font-size-13 mb-1">10GB</p>
                                                    <p class="text-muted font-size-13 mb-1 text-truncate">20GB used</p>
                                                </div>
                                                <div class="progress animated-progess custom-progress">
                                                    <div class="progress-bar bg-gradient bg-primary" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="70">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                        </div>
                        <!-- end row -->

                        <h5 class="font-size-16 me-3 mb-0">Folders</h5>

                        <div class="row mt-4">
                            <div class="col-xl-4 col-sm-6">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bx bxs-folder h2 mb-0 text-warning"></i>
                                                </div>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-4.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title rounded-circle bg-success text-white font-size-15">
                                                                    A
                                                                </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex mt-3">
                                                <div class="overflow-hidden me-auto">
                                                    <h5 class="font-size-15 text-truncate mb-1"><a href="javascript: void(0);" class="text-body">Analytics</a></h5>
                                                    <p class="text-muted text-truncate mb-0">12 Files</p>
                                                </div>
                                                <div class="align-self-end ms-2">
                                                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-time-five align-middle me-1"></i> 15 min ago</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-xl-4 col-sm-6">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bx bxs-folder h2 mb-0 text-warning"></i>
                                                </div>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-3.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-6.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex mt-3">
                                                <div class="overflow-hidden me-auto">
                                                    <h5 class="font-size-15 text-truncate mb-1"><a href="javascript: void(0);" class="text-body">Sketch Design</a></h5>
                                                    <p class="text-muted text-truncate mb-0">235 Files</p>
                                                </div>
                                                <div class="align-self-end ms-2">
                                                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-time-five align-middle me-1"></i> 23 min ago</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-xl-4 col-sm-6">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bx bxs-folder h2 mb-0 text-warning"></i>
                                                </div>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title rounded-circle bg-info text-white font-size-15">
                                                                    K
                                                                </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-2.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex mt-3">
                                                <div class="overflow-hidden me-auto">
                                                    <h5 class="font-size-15 text-truncate mb-1"><a href="javascript: void(0);" class="text-body">Applications</a></h5>
                                                    <p class="text-muted text-truncate mb-0">20 Files</p>
                                                </div>
                                                <div class="align-self-end ms-2">
                                                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-time-five align-middle me-1"></i> 45 min ago</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                        </div>
                        <!-- End row -->

                        <h5 class="font-size-16 me-3 mb-0">Co-owners</h5>

                        <div class="row mt-4">
                            <div class="col-xl-4 col-sm-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar align-self-center me-3">
                                                    <img src="{{ asset('assets/images/users/avatar-3.jpg') }}" alt="" class="avatar rounded">
                                                </div>
                                                <div class="flex-1">
                                                    <h5 class="font-size-15 mb-1">Sadie Stanfield</h5>
                                                    <a href="" class="font-size-14 text-muted"><u>Con Edit</u></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-xl-4 col-sm-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar align-self-center me-3">
                                                    <img src="{{ asset('assets/images/users/avatar-5.jpg') }}" alt="" class="avatar rounded">
                                                </div>

                                                <div class="flex-1">
                                                    <h5 class="font-size-15 mb-1">Dustin Westrick</h5>
                                                    <a href="" class="font-size-14 text-muted"><u>Con View</u></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-xl-4 col-sm-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar align-self-center me-3">
                                                    <img src="{{ asset('assets/images/users/avatar-6.jpg') }}" alt="" class="avatar rounded">
                                                </div>

                                                <div class="flex-1">
                                                    <h5 class="font-size-15 mb-1">Patrick Parker</h5>
                                                    <a href="" class="font-size-14 text-muted"><u>Con Edit</u></a>
                                                </div>
                                            
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                        </div>

                        <h5 class="font-size-16 me-3 mb-0">Recent Files</h5>

                       
                        <div class="mx-n4 px-4 mt-4" data-simplebar style="max-height: 350px;">
                            <div class="table-responsive">
                           
                                <table class="table align-middle table-nowrap table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                          <th scope="col">Name</th>
                                          <th scope="col">Date modified</th>
                                          <th scope="col">Size</th>
                                          <th scope="col" colspan="2">Members</th>
                                        </tr>
                                      </thead>
                                    <tbody>
                                        <tr>
                                            <td><a href="javascript: void(0);" class="text-dark  fw-medium"><i class="mdi mdi-file-document font-size-20 align-middle text-primary me-2"></i> index.html</a></td>
                                            <td>12-10-2021</td>
                                            <td>09 KB</td>
                                            <td>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-4.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-5.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title rounded-circle bg-success text-white font-size-15">
                                                                    A
                                                                </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                        <i class="mdi mdi-dots-horizontal"></i>
                                                    </a>
                                                    
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Open</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Rename</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="javascript: void(0);" class="text-dark  fw-medium"><i class="mdi mdi-folder-zip font-size-20 align-middle text-warning me-2"></i> Project-A.zip</a></td>
                                            <td>11-10-2021</td>
                                            <td>115 KB</td>
                                            <td>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-8.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-2.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                        <i class="mdi mdi-dots-horizontal"></i>
                                                    </a>
                                                    
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Open</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Rename</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="javascript: void(0);" class="text-dark  fw-medium"><i class="mdi mdi-image font-size-20 align-middle text-muted me-2"></i> Img-1.jpeg</a></td>
                                            <td>11-10-2021</td>
                                            <td>86 KB</td>
                                            <td>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title rounded-circle bg-info text-white font-size-15">
                                                                    K
                                                                </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-2.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                        <i class="mdi mdi-dots-horizontal"></i>
                                                    </a>
                                                    
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Open</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Rename</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="javascript: void(0);" class="text-dark  fw-medium"><i class="mdi mdi-text-box font-size-20 align-middle text-muted me-2"></i> update list.txt</a></td>
                                            <td>10-10-2021</td>
                                            <td>08 KB</td>
                                            <td>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-6.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-7.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                        <i class="mdi mdi-dots-horizontal"></i>
                                                    </a>
                                                    
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Open</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Rename</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="javascript: void(0);" class="text-dark  fw-medium"><i class="mdi mdi-folder font-size-20 align-middle text-warning me-2"></i> Project B</a></td>
                                            <td>10-10-2021</td>
                                            <td>72 KB</td>
                                            <td>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-3.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title rounded-circle bg-danger text-white font-size-15">
                                                                    3+
                                                                </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                        <i class="mdi mdi-dots-horizontal"></i>
                                                    </a>
                                                    
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Open</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Rename</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="javascript: void(0);" class="text-dark  fw-medium"><i class="mdi mdi-text-box font-size-20 align-middle text-muted me-2"></i> Changes list.txt</a></td>
                                            <td>09-10-2021</td>
                                            <td>07 KB</td>
                                            <td>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-4.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-5.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                        <i class="mdi mdi-dots-horizontal"></i>
                                                    </a>
                                                    
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Open</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Rename</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="javascript: void(0);" class="text-dark  fw-medium"><i class="mdi mdi-image font-size-20 align-middle text-success me-2"></i> Img-2.png</a></td>
                                            <td>09-10-2021</td>
                                            <td>31 KB</td>
                                            <td>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title rounded-circle bg-pink text-white font-size-15">
                                                                    L
                                                                </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-2.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                        <i class="mdi mdi-dots-horizontal"></i>
                                                    </a>
                                                    
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Open</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Rename</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="javascript: void(0);" class="text-dark  fw-medium"><i class="mdi mdi-folder font-size-20 align-middle text-warning me-2"></i> Project C</a></td>
                                            <td>09-10-2021</td>
                                            <td>20 KB</td>
                                            <td>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-4.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-5.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title rounded-circle bg-success text-white font-size-15">
                                                                    A
                                                                </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                        <i class="mdi mdi-dots-horizontal"></i>
                                                    </a>
                                                    
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Open</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Rename</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="javascript: void(0);" class="text-dark  fw-medium"><i class="bx bxs-file font-size-20 align-middle text-primary me-2"></i> starter-page.html</a></td>
                                            <td>08-10-2021</td>
                                            <td>11 KB</td>
                                            <td>
                                                <div class="avatar-group">
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-8.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                    <div class="avatar-group-item">
                                                        <a href="javascript: void(0);" class="d-inline-block">
                                                            <img src="{{ asset('assets/images/users/avatar-2.jpg') }}" alt="" class="rounded-circle avatar-xs">
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                        <i class="mdi mdi-dots-horizontal"></i>
                                                    </a>
                                                    
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Open</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Rename</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#">Remove</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                       
                    </div>
                </div>
                <!-- end card -->
            </div>
            <!-- end w-100 -->
        </div>
    </div>

    <div class="card filemanager-sidebar ms-lg-3">
        <div class="card-body">
            <div class="d-flex flex-column h-100">

                <div class="mb-4">
                    <h5 class="font-size-16 me-3 mb-0">Activity Chart</h5>
                    <div class="text-center">
                        <div id="overview" data-colors='["--bs-success", "--bs-danger","--bs-info","--bs-primary", "--bs-warning"]' class="apex-chart"></div>
                    </div>

                    <h5 class="font-size-16 mb-0">Recent Files</h5>

                    <div class="mt-4">
                        <div class="pb-2 mb-2">
                            <a href="javascript: void(0);" class="text-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar align-self-center me-3">
                                        <div class="avatar-title rounded bg-success-subtle text-success font-size-24">
                                            <i class="mdi mdi-image"></i>
                                        </div>
                                    </div>
                                    <div class="overflow-hidden me-auto">
                                        <h5 class="font-size-15 text-truncate mb-1">Images</h5>
                                        <p class="text-muted text-truncate mb-0">1,876 Files</p>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted font-size-14">8.4 GB</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="py-2 mb-2">
                            <a href="javascript: void(0);" class="text-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar align-self-center me-3">
                                        <div class="avatar-title rounded bg-danger-subtle text-danger font-size-24">
                                            <i class="mdi mdi-play-circle-outline"></i>
                                        </div>
                                    </div>
                                    <div class="overflow-hidden me-auto">
                                        <h5 class="font-size-15 text-truncate mb-1">Video</h5>
                                        <p class="text-muted text-truncate mb-0">45 Files</p>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted font-size-14">4.1 GB</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="py-2 mb-2">
                            <a href="javascript: void(0);" class="text-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar align-self-center me-3">
                                        <div class="avatar-title rounded bg-info-subtle text-info font-size-24">
                                            <i class="mdi mdi-music"></i>
                                        </div>
                                    </div>
                                    <div class="overflow-hidden me-auto">
                                        <h5 class="font-size-15 text-truncate mb-1">Music</h5>
                                        <p class="text-muted text-truncate mb-0">21 Files</p>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted font-size-14">3.2 GB</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="py-2 mb-2">
                            <a href="javascript: void(0);" class="text-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar align-self-center me-3">
                                        <div class="avatar-title rounded bg-primary-subtle text-primary font-size-24">
                                            <i class="mdi mdi-file-document"></i>
                                        </div>
                                    </div>
                                    <div class="overflow-hidden me-auto">
                                        <h5 class="font-size-15 text-truncate mb-1">Document</h5>
                                        <p class="text-muted text-truncate mb-0">21 Files</p>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted font-size-14">2 GB</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="py-2 mb-2">
                            <a href="javascript: void(0);" class="text-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar align-self-center me-3">
                                        <div class="avatar-title rounded bg-warning-subtle text-warning font-size-24">
                                            <i class="mdi mdi-folder"></i>
                                        </div>
                                    </div>
                                    <div class="overflow-hidden me-auto">
                                        <h5 class="font-size-15 text-truncate mb-1">Others</h5>
                                        <p class="text-muted text-truncate mb-0">20 Files</p>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted font-size-14">1.4 GB</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-5 mt-auto">
                    <form action="#" class="dropzone">
                        <div class="fallback">
                            <input name="file" type="file" multiple="multiple">
                        </div>
                        <div class="dz-message needsclick">
                            <div class="mb-3">
                                <i class="display-4 text-muted mdi mdi-cloud-upload"></i>
                            </div>
                            
                            <h5>Import Files</h5>
                        </div>
                    </form>
                </div>

            </div>


        </div>
    </div>
</div>
<!-- end -->

@endsection
@section('script')
<script src="{{ asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('/assets/libs/dropzone/dropzone.min.js') }}"></script>
<script src="{{ asset('/assets/js/pages/file-manager.init.js') }}"></script>
@endsection
