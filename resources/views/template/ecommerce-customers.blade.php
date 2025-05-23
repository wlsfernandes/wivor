@extends('layouts.master')
@section('title')
    @lang('translation.Customers')
@endsection
@section('css')
    <!-- DataTables -->
    <link href="{{ asset('assets/libs/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @component('common-components.breadcrumb')
        @slot('pagetitle') Ecommerce @endslot
        @slot('title') Customers @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div>
                        <div>
                            <button type="button" class="btn btn-success waves-effect waves-light mb-3"><i class="mdi mdi-plus me-1"></i> Add customers</button> 
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-centered datatable dt-responsive nowrap table-card-list" style="border-collapse: collapse; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck">
                                                <label class="form-check-label" for="customercheck"></label>
                                            </div>
                                        </th>
                                        <th style="width: 120px;">Customer ID</th>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Join Date</th>
                                        <th>Status</th>
                                        <th style="width: 120px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck1">
                                                <label class="form-check-label" for="customercheck1"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0123</a> </td>
                                        <td>
                                            <img src="{{ asset('assets/images/users/avatar-2.jpg') }}" alt="" class="avatar-xs rounded-circle me-2">
                                            <span>William Shipp</span>
                                        </td>
                                        <td>WilliamShipp@jourrapide.com</td>
                                        
                                        <td>
                                            14 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-success-subtle text-success font-size-12">Active</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck2">
                                                <label class="form-check-label" for="customercheck2"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0122</a> </td>
                                        <td>
                                            <div class="avatar-xs d-inline-block me-2">
                                                <span class="avatar-title rounded-circle bg-light text-body">
                                                    J
                                                </span>
                                            </div>
                                            <span>Joe Hardy</span>
                                        </td>
                                        <td>JoeHardy@dayrep.com</td>
                                        
                                        <td>
                                            14 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-success-subtle text-success font-size-12">Active</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck3">
                                                <label class="form-check-label" for="customercheck3"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0121</a> </td>
                                        <td>
                                            <img src="{{ asset('assets/images/users/avatar-3.jpg') }}" alt="" class="avatar-xs rounded-circle me-2">
                                            <span>Thomas Hankins</span>
                                        </td>
                                        <td>ThomasHankins@dayrep.com</td>
                                        
                                        <td>
                                            13 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-success-subtle text-success font-size-12">Active</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck4">
                                                <label class="form-check-label" for="customercheck4"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0120</a> </td>
                                        <td>
                                            <img src="{{ asset('assets/images/users/avatar-4.jpg') }}" alt="" class="avatar-xs rounded-circle me-2">
                                            <span>Mary Frazier</span>
                                        </td>
                                        <td>MaryFrazier@armyspy.com</td>
                                        
                                        <td>
                                            12 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-danger-subtle text-danger font-size-12">Deactive</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck5">
                                                <label class="form-check-label" for="customercheck5"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0119</a> </td>
                                        <td>
                                            <div class="avatar-xs d-inline-block me-2">
                                                <span class="avatar-title rounded-circle bg-light text-body">
                                                    S
                                                </span>
                                            </div>
                                            <span>Sam Perry</span>
                                        </td>
                                        <td>SamPerry@rhyta.com</td>
                                        
                                        <td>
                                            12 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-success-subtle text-success font-size-12">Active</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck6">
                                                <label class="form-check-label" for="customercheck6"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0118</a> </td>
                                        <td>
                                            <img src="{{ asset('assets/images/users/avatar-5.jpg') }}" alt="" class="avatar-xs rounded-circle me-2">
                                            <span>Muriel Myers</span>
                                        </td>
                                        <td>MurielMyers@rhyta.com</td>
                                        
                                        <td>
                                            09 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-danger-subtle text-danger font-size-12">Deactive</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck7">
                                                <label class="form-check-label" for="customercheck7"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0117</a> </td>
                                        <td>
                                            <div class="avatar-xs d-inline-block me-2">
                                                <span class="avatar-title rounded-circle bg-light text-body">
                                                    J
                                                </span>
                                            </div>
                                            <span>Jessie Jacobs</span>
                                        </td>
                                        <td>JessieJacobs@teleworm.us</td>
                                        
                                        <td>
                                            09 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-success-subtle text-success  font-size-12">Active</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck8">
                                                <label class="form-check-label" for="customercheck8"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0116</a> </td>
                                        <td>
                                            <img src="{{ asset('assets/images/users/avatar-6.jpg') }}" alt="" class="avatar-xs rounded-circle me-2">
                                            <span>Edward King</span>
                                        </td>
                                        <td>EdwardKing@teleworm.us</td>
                                        
                                        <td>
                                            08 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-success-subtle text-success font-size-12">Active</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck9">
                                                <label class="form-check-label" for="customercheck9"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0115</a> </td>
                                        <td>
                                            <img src="{{ asset('assets/images/users/avatar-7.jpg') }}" alt="" class="avatar-xs rounded-circle me-2">
                                            <span>Stacy Webster</span>
                                        </td>
                                        <td>StacyWebster@armyspy.com</td>
                                        
                                        <td>
                                            07 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-danger-subtle text-danger font-size-12">Deactive</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck10">
                                                <label class="form-check-label" for="customercheck10"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0114</a> </td>
                                        <td>
                                            <div class="avatar-xs d-inline-block me-2">
                                                <span class="avatar-title rounded-circle bg-light text-body">
                                                    A
                                                </span>
                                            </div>
                                            <span>Amy McDonald</span>
                                        </td>
                                        <td>AmyMcDonald@jourrapide.com</td>
                                        
                                        <td>
                                            05 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-success-subtle text-success font-size-12">Active</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck11">
                                                <label class="form-check-label" for="customercheck11"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0113</a> </td>
                                        <td>
                                            <div class="avatar-xs d-inline-block me-2">
                                                <span class="avatar-title rounded-circle bg-light text-body">
                                                    T
                                                </span>
                                            </div>
                                            <span>Terry Brown</span>
                                        </td>
                                        <td>TerryBrown@dayrep.com</td>
                                        
                                        <td>
                                            02 Apr, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-success-subtle text-success font-size-12">Active</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" class="form-check-input" id="customercheck12">
                                                <label class="form-check-label" for="customercheck12"></label>
                                            </div>
                                        </td>
                                        
                                        <td><a href="javascript: void(0);" class="text-reset  fw-bold">#MN0112</a> </td>
                                        <td>
                                            <img src="{{ asset('assets/images/users/avatar-8.jpg') }}" alt="" class="avatar-xs rounded-circle me-2">
                                            <span>Crissy Holland</span>
                                        </td>
                                        <td>CrissyHolland@armyspy.com</td>
                                        
                                        <td>
                                            23 Mar, 2020
                                        </td>
                                        <td>
                                            <div class="badge bg-pill bg-danger-subtle text-danger font-size-12">Deactive</div>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                            <a href="javascript:void(0);" class="px-3 text-danger"><i class="uil uil-trash-alt font-size-18"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

@endsection
@section('script')
    <script src="{{ asset('/assets/libs/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/ecommerce-datatables.init.js') }}"></script>
@endsection
