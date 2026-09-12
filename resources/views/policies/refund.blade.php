@extends('layouts.app')

@section('title', 'Refund Policy | WivorPhotos')
@section('meta-description', 'How to request support and how WivorPhotos handles refunds for digital photograph purchases.')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <article class="col-lg-8">
                <h1 class="mb-3">Refund Policy</h1>
                <p class="text-muted fw-semibold">Last updated: September 12, 2026</p>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4 p-md-5">
                        <p>WivorPhotos sells digital JPEG photographs delivered through time-limited download access. Refund requests are reviewed individually through support because the current service does not provide an automated in-app refund-request or refund-management interface.</p>

                        <h2 class="h4 mt-4">Requesting help or a refund</h2>
                        <p><a href="{{ route('contact_us') }}">Contact WivorPhotos support</a> and include the order number, the email address used at Stripe Checkout, the affected photograph, and a description of the issue. Examples appropriate for review include duplicate charges, an unavailable or corrupted purchased file, a materially incorrect delivered file, or a technical problem that prevents access during the stated download period.</p>

                        <h2 class="h4 mt-4">Review and processing</h2>
                        <p>Support reviews the order and available delivery records. Submitting a request does not create an instant refund or guarantee approval. Approved refunds are initiated through Stripe or handled with Stripe support and are returned through the applicable payment process. Stripe and the customer's financial institution control when credited funds appear.</p>

                        <h2 class="h4 mt-4">Full and partial refunds</h2>
                        <p>When Stripe reports a full refund, WivorPhotos marks the order refunded and revokes future download entitlement for the order. Partial refunds are not currently represented by a complete automated item-level workflow in WivorPhotos and therefore require manual review and communication.</p>

                        <h2 class="h4 mt-4">Payment disputes</h2>
                        <p>If Stripe reports a payment dispute, WivorPhotos may revoke future download access while the dispute is handled. Customers are encouraged to contact support first so WivorPhotos can investigate download or order issues.</p>

                        <h2 class="h4 mt-4">Photo removal</h2>
                        <p>A request to remove a photograph from a gallery is reviewed separately from a refund request. Use the <a href="{{ route('photo-removal.create') }}">Photo Removal Request</a> process for a privacy, rights, safety, or content concern, and contact support with the order number if the concern also affects a purchase.</p>

                        <h2 class="h4 mt-4">Policy updates</h2>
                        <p class="mb-0">WivorPhotos may update this policy as its support and refund processes change. The date above identifies the current published version.</p>
                    </div>
                </div>
            </article>
        </div>
    </main>
@endsection
