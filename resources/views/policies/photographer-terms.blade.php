@extends('layouts.app')

@section('title', 'Photographer Terms | WivorPhotos')
@section('meta-description', 'Terms for photographers who apply, publish event photographs, and earn through WivorPhotos.')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <article class="col-lg-8">
                <h1 class="mb-3">Photographer Terms</h1>
                <p class="text-muted fw-semibold">Last updated: September 12, 2026</p>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4 p-md-5">
                        <p>These Photographer Terms apply to applicants and approved photographers who use WivorPhotos to upload, publish, and sell event photographs. The public <a href="{{ route('terms') }}">Terms of Use</a> govern customer and visitor activity.</p>

                        <h2 class="h4 mt-4">Eligibility, application, and account</h2>
                        <p>Applicants must be at least 18 years old and provide accurate application, contact, portfolio, equipment, and account information. Application submission does not guarantee approval. Account access is personal to the approved photographer and must be kept secure.</p>

                        <h2 class="h4 mt-4">Rights and event authority</h2>
                        <p>A photographer may upload only photographs that the photographer owns or is authorized to upload, license, display, and sell. The photographer is responsible for having the authority and permissions needed to photograph the event and for complying with event rules, applicable law, and any restrictions communicated for an assignment.</p>

                        <h2 class="h4 mt-4">Assignments, uploads, and prohibited content</h2>
                        <p>Photographers may upload only to events they own or have been assigned to cover and must meet the applicable upload deadline. The current service accepts qualifying JPEG files and may reject unsupported, corrupt, duplicate, undersized, oversized, or otherwise invalid files.</p>
                        <p>Photographers must not upload unlawful, infringing, deceptive, abusive, exploitative, or unsafe content; content outside the applicable event assignment; or content they do not have the right to sell. WivorPhotos may review, reject, unpublish, or remove content and may preserve records needed to address a complaint or dispute.</p>

                        <h2 class="h4 mt-4">License to operate the marketplace</h2>
                        <p>By uploading a photograph, the photographer authorizes WivorPhotos and its service providers to store, process, reproduce, resize, watermark, display, market, offer for sale, and deliver that photograph as needed to operate WivorPhotos and fulfill customer purchases. This authorization includes creating previews and thumbnails, storing media through Amazon S3, and using Amazon Rekognition for the recognition functions described below. Ownership is not transferred to WivorPhotos by this operational license.</p>

                        <h2 class="h4 mt-4">Bib and face recognition</h2>
                        <p>Uploaded event photographs may be analyzed by Amazon Rekognition to detect visible bib numbers. When face search is enabled, published photographs may also be processed to create face metadata associated with the photograph's identifier in an event-specific Rekognition collection. Customer search selfies follow a separate voluntary consent flow and are not intentionally stored by WivorPhotos in S3, its database, the customer's session, or a queue payload.</p>
                        <p>When photographs are unpublished, removed, or expire through the current retention process, WivorPhotos queues removal of their associated face metadata. Provider availability and retry processing can affect completion time. More information appears in the <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>

                        <h2 class="h4 mt-4">Publication, pricing, commission, and earnings</h2>
                        <p>Photographers choose ready photographs to publish. The event's displayed per-photo price applies at checkout. For each order, WivorPhotos records the price, its configured commission, and the resulting photographer allocation when the order is created. The dashboard reports gross sales, commission, net earnings, and payout status.</p>

                        <h2 class="h4 mt-4">Payouts and Stripe Connect</h2>
                        <p>Photographers may be asked to complete Stripe Connect onboarding and maintain accurate payout information. WivorPhotos tracks payout readiness and transfers separately; it does not currently promise an automatic transfer after each sale. A dashboard amount marked pending is not confirmation that funds have already been transferred. Any applicable payout schedule, review, adjustment, or required action will be handled separately.</p>

                        <h2 class="h4 mt-4">Taxes</h2>
                        <p>Photographers must provide accurate information requested for payment or tax reporting and should obtain independent advice about taxes that may apply to their earnings. WivorPhotos does not provide tax advice through the platform.</p>

                        <h2 class="h4 mt-4">Removal and retention</h2>
                        <p>Photographers can remove eligible unpublished, unsold photographs. Published photographs may be unpublished or removed through administrative review, including in response to a <a href="{{ route('photo-removal.create') }}">Photo Removal Request</a>. Gallery media is subject to the configured sale window, purchase-protection periods, administrative holds, and scheduled deletion. Photographers should retain their own backups; WivorPhotos is not permanent archival storage.</p>

                        <h2 class="h4 mt-4">Suspension and termination</h2>
                        <p>WivorPhotos may decline an application or suspend or end photographer access for rights, safety, security, payment, event, content, or policy concerns. Content and records may be retained or removed as needed for outstanding orders, disputes, support, accounting, or applicable obligations.</p>

                        <h2 class="h4 mt-4">Contact and updates</h2>
                        <p class="mb-0">Photographers can <a href="{{ route('contact_us') }}">contact WivorPhotos</a> with questions. WivorPhotos may update these Terms as the service changes; the date above identifies the current published version.</p>
                    </div>
                </div>
            </article>
        </div>
    </main>
@endsection
