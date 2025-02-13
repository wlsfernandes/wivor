@extends('layouts.master')
@section('title')
@lang('translation.Colors')
@endsection

@section('content')
@component('common-components.breadcrumb')
@slot('pagetitle') UI Elements @endslot
@slot('title') Colors @endslot
@endcomponent

<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 font-size-18 text-center">Hex : #5b73e8</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-primary-subtle mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-primary-subtle</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-gradient-primary mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-gradient-primary</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-primary mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-primary</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Hex : #34c38f</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-success-subtle mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-success-subtle</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-gradient-success mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-gradient-success</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-success mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-success</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Hex : #50a5f1</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-info-subtle mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-info-subtle</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-gradient-info mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-gradient-info</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-info mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-info</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Hex : #f1b44c</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-warning-subtle mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-warning-subtle</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-gradient-warning mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-gradient-warning</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-warning mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-warning</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Hex : #f46a6a</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-danger-subtle mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-danger-subtle</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-gradient-danger mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-gradient-danger</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-danger mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-danger</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Hex : #343a40</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-dark-subtle mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-dark-subtle</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-gradient-dark mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-gradient-dark</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-dark mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-dark</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Hex : #f5f6f8</h5>
                <div class="row">

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-light-subtle mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-light-subtle</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-gradient-light mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-gradient-light</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-light mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-light</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Hex : #74788d</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-secondary-subtle mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-secondary-subtle</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-gradient-secondary mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-gradient-secondary</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-secondary mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-secondary</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Body</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-body mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-body</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-body-tertiary mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-body-tertiary</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-body-secondary mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-body-secondary</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Opacity</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-primary opacity-75 mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">opacity-75</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-primary opacity-50 mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">opacity-50</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-primary opacity-25 mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">opacity-25</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Other</h5>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-black mx-auto rounded my-2"></div>
                            <h6 class="text-muted mt-3">bg-black</h6>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-white mx-auto rounded my-2 border"></div>
                            <h6 class="text-muted mt-3">bg-white</h6>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center">
                            <div class="avatar-md bg-transparent mx-auto rounded my-2 border"></div>
                            <h6 class="text-muted mt-3">bg-transparent</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 text-center">Text Color</h5>
                <div class="row">
                    <div class="col">
                        <div class="">
                            <h6 class="text-primary mt-3">text-primary</h6>
                            <h6 class="text-primary-emphasis mt-3">text-primary-emphasis</h6>
                            <h6 class="text-secondary mt-3">text-secondary</h6>
                            <h6 class="text-secondary-emphasis mt-3">text-secondary-emphasis</h6>
                            <h6 class="text-success mt-3">text-success</h6>
                        </div>
                    </div>

                    <div class="col">
                        <h6 class="text-success-emphasis mt-3">text-success-emphasis</h6>
                        <h6 class="text-info mt-3">text-info</h6>
                        <h6 class="text-info-emphasis mt-3">text-info-emphasis</h6>
                        <h6 class="text-warning mt-3">text-warning</h6>
                    </div>

                    <div class="col">
                        <div class="">
                            <h6 class="text-warning-emphasis mt-3">text-warning-emphasis</h6>
                            <h6 class="text-danger mt-3">text-danger</h6>
                            <h6 class="text-danger-emphasis mt-3">text-danger-emphasis</h6>
                            <h6 class="text-reset  mt-3">text-dark </h6>
                        </div>
                    </div>

                    <div class="col">
                        <h6 class="text-dark-emphasis mt-3">text-dark -emphasis</h6>
                        <h6 class="text-white bg-dark mt-3">text-light</h6>
                        <h6 class="text-light-emphasis mt-3">text-light-emphasis</h6>
                        <h6 class="text-body mt-3">text-body</h6>
                    </div>

                    <div class="col">
                        <div class="">
                            <h6 class="text-muted mt-3">text-muted</h6>
                            <h6 class="text-body-emphasis mt-3">text-body-emphasis</h6>
                            <h6 class="text-body-secondary mt-3">text-body-secondary</h6>
                            <h6 class="text-body-tertiary mt-3">text-body-tertiary</h6>
                        </div>
                    </div>

                    <div class="col">
                        <h6 class="text-black mt-3">text-black</h6>
                        <h6 class="text-white bg-dark mt-3">text-white</h6>
                        <h6 class="text-black-50 mt-3">text-black-50</h6>
                        <h6 class="text-white-50 b bg-dark mt-3">text-white-50</h6>
                    </div>

                    <div class="col">
                        <div>
                            <h6 class="text-body text-opacity-100 mt-3">text-opacity-100</h6>
                            <h6 class="text-body text-opacity-75 mt-3">text-opacity-75</h6>
                            <h6 class="text-body text-opacity-50 mt-3">text-opacity-50</h6>
                            <h6 class="text-body text-opacity-25 mt-3">text-opacity-25</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

</div> <!-- container-fluid -->
</div>
<!-- End Page-content -->

@endsection
