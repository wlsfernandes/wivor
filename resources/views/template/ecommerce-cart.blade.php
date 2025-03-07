@extends('layouts.master')
@section('title')
@lang('translation.Cart')
@endsection
@section('css')
<!-- DataTables -->
<link href="{{ asset('/assets/libs/bootstrap-touchspin/bootstrap-touchspin.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
@component('common-components.breadcrumb')
@slot('pagetitle') Ecommerce @endslot
@slot('title') Cart @endslot
@endcomponent

<div class="row mb-3">
    <div class="col-xl-8">
        <div class="card border shadow-none">
            <div class="card-body">

                <div class="d-flex align-items-start border-bottom pb-3">
                    <div class="flex-shrink-0 me-4">
                        <img src="{{asset('assets/images/product/img-1.png')}}" alt="" class="avatar-lg">
                    </div>
                    <div class="flex-grow-1 align-self-center overflow-hidden">
                        <div>
                            <h5 class="text-truncate font-size-16"><a href="ecommerce-product-detail" class="text-reset">Nike N012 Running Shoes</a></h5>
                            <p class="mb-1">Color : <span class="fw-medium">Gray</span></p>
                            <p>Size : <span class="fw-medium">08</span></p>
                        </div>
                    </div>
                    <div class="ms-2">
                        <ul class="list-inline mb-0 font-size-16">
                            <li class="list-inline-item">
                                <a href="#" class="text-muted px-2">
                                    <i class="uil uil-trash-alt"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" class="text-muted px-2">
                                    <i class="uil uil-heart"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mt-3">
                                <p class="text-muted mb-2">Price</p>
                                <h5 class="font-size-16">$260</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-3">
                                <p class="text-muted mb-2">Quantity</p>
                                <div style="width: 110px;" class="product-cart-touchspin">
                                    <input data-toggle="touchspin" type="text" value="02">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-3">
                                <p class="text-muted mb-2">Total</p>
                                <h5 class="font-size-16">$520</h5>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- end card -->

        <div class="card border shadow-none">
            <div class="card-body">

                <div class="d-flex align-items-start border-bottom pb-3">
                    <div class="flex-shrink-0 me-4">
                        <img src="{{asset('assets/images/product/img-2.png')}}" alt="" class="avatar-lg">
                    </div>
                    <div class="flex-grow-1 align-self-center overflow-hidden">
                        <div>
                            <h5 class="text-truncate font-size-16"><a href="ecommerce-product-detail" class="text-reset">Adidas Running Shoes</a></h5>
                            <p class="mb-1">Color : <span class="fw-medium">Black</span></p>
                            <p>Size : <span class="fw-medium">09</span></p>
                        </div>
                    </div>
                    <div class="ms-2">
                        <ul class="list-inline mb-0 font-size-16">
                            <li class="list-inline-item" data-bs-toggle="tooltip" data-placement="top" title="Remove">
                                <a href="#" class="text-muted px-2">
                                    <i class="uil uil-trash-alt"></i>
                                </a>
                            </li>
                            <li class="list-inline-item" data-bs-toggle="tooltip" data-placement="top" title="Add Wishlist">
                                <a href="#" class="text-muted px-2">
                                    <i class="uil uil-heart"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mt-3">
                                <p class="text-muted mb-2">Price</p>
                                <h5 class="font-size-16">$260</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-3">
                                <p class="text-muted mb-2">Quantity</p>
                                <div style="width: 110px;" class="product-cart-touchspin">
                                    <input data-toggle="touchspin" type="text" value="01">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-3">
                                <p class="text-muted mb-2">Total</p>
                                <h5 class="font-size-16">$260</h5>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- end card -->

        <div class="row mt-4">
            <div class="col-sm-6">
                <a href="ecommerce-products" class="btn btn-link text-muted">
                    <i class="uil uil-arrow-left me-1"></i> Continue Shopping </a>
            </div> <!-- end col -->
            <div class="col-sm-6">
                <div class="text-sm-end mt-2 mt-sm-0">
                    <a href="ecommerce-checkout" class="btn btn-success">
                        <i class="uil uil-shopping-cart-alt me-1"></i> Checkout </a>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row-->
    </div>

    <div class="col-xl-4">
        <div class="mt-5 mt-lg-3">
            <div class="card border shadow-none">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h5 class="font-size-16 mb-0">Order Summary <span class="float-end">#MN0124</span></h5>
                </div>
                <div class="card-body p-4">

                    <div class="table-responsive">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td>Sub Total :</td>
                                    <td class="text-end">$ 780</td>
                                </tr>
                                <tr>
                                    <td>Discount : </td>
                                    <td class="text-end">- $ 78</td>
                                </tr>
                                <tr>
                                    <td>Shipping Charge :</td>
                                    <td class="text-end">$ 25</td>
                                </tr>
                                <tr>
                                    <td>Estimated Tax : </td>
                                    <td class="text-end">$ 18.20</td>
                                </tr>
                                <tr class="bg-light">
                                    <th>Total :</th>
                                    <td class="text-end">
                                        <span class="fw-bold">
                                            $ 745.2
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- end table-responsive -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

@endsection
@section('script')
<script src="{{ asset('/assets/libs/bootstrap-touchspin/bootstrap-touchspin.min.js')}}"></script>
<script src="{{ asset('/assets/js/pages/ecommerce-cart.init.js')}}"></script>
@endsection
