@extends('layouts.master')

@section('title', 'Payment Process Manual | WivorPhotos')

@section('content')
    @component('common-components.breadcrumb')
        @slot('pagetitle')
            Payments
        @endslot
        @slot('title')
            Payment process manual
        @endslot
    @endcomponent

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-primary mb-2">Administrator guide</span>
                    <h1 class="h3 mb-2">How customer payments and photographer payouts work</h1>
                    <p class="text-muted mb-0">
                        This manual follows money from the customer's Stripe Checkout payment, through WivorPhotos,
                        to each photographer's connected Stripe account and then to their bank.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                    <a class="btn btn-primary" href="{{ route('payments.index') }}">
                        <i class="fa fa-receipt me-1" aria-hidden="true"></i> View Photo Orders
                    </a>
                    <a class="btn btn-outline-primary" href="{{ route('photographers.list') }}">
                        <i class="fa fa-camera me-1" aria-hidden="true"></i> View Photographers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-danger border-0 shadow-sm" role="alert">
        <div class="d-flex">
            <i class="fa fa-exclamation-triangle font-size-20 me-3 mt-1" aria-hidden="true"></i>
            <div>
                <h2 class="h5 alert-heading">Important refund limitation</h2>
                <p class="mb-0">
                    WivorPhotos marks fully refunded or disputed orders and revokes future downloads, but it does
                    <strong>not</strong> automatically reverse money already transferred to a photographer. Every
                    refund or dispute must include a manual review in Stripe. Do not assume that changing the local
                    order status recovers a completed Connect transfer.
                </p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">The complete money flow</h2>
            <p class="text-muted mb-0">There are three separate movements of money. Only the middle movement is created by WivorPhotos.</p>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="border rounded h-100 p-3">
                        <span class="badge rounded-pill bg-primary mb-3">1</span>
                        <h3 class="h5">Customer pays WivorPhotos</h3>
                        <p class="text-muted mb-0">
                            The customer pays on Stripe-hosted Checkout. The charge is created on the WivorPhotos
                            platform Stripe account. The photographer is not charged directly and this is not a
                            destination charge.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded h-100 p-3">
                        <span class="badge rounded-pill bg-primary mb-3">2</span>
                        <h3 class="h5">WivorPhotos transfers each share</h3>
                        <p class="text-muted mb-0">
                            After Stripe confirms the order is paid, WivorPhotos groups the purchased photos by
                            photographer and creates one Connect transfer for each photographer's stored allocation.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded h-100 p-3">
                        <span class="badge rounded-pill bg-primary mb-3">3</span>
                        <h3 class="h5">Stripe pays bank accounts</h3>
                        <p class="text-muted mb-0">
                            Stripe moves the connected-account balance to the photographer's bank according to that
                            account's Stripe payout schedule. Stripe separately pays the platform balance to Wivor's
                            bank according to Wivor's platform payout settings.
                        </p>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Movement</th>
                            <th scope="col">Created by</th>
                            <th scope="col">Source</th>
                            <th scope="col">Destination</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Customer charge</td>
                            <td>Stripe Checkout</td>
                            <td>Customer's payment method</td>
                            <td>WivorPhotos platform Stripe balance</td>
                        </tr>
                        <tr>
                            <td>Photographer transfer</td>
                            <td>WivorPhotos queue job</td>
                            <td>The customer's platform charge</td>
                            <td>Photographer's connected Stripe balance</td>
                        </tr>
                        <tr>
                            <td>Bank payout</td>
                            <td>Stripe payout schedule</td>
                            <td>Stripe account balance</td>
                            <td>Account holder's bank account</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" id="photographer-setup">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">1. Photographer approval and Stripe connection</h2>
            <p class="text-muted mb-0">A photographer must connect Stripe before WivorPhotos can send their allocation.</p>
        </div>
        <div class="card-body">
            <ol class="mb-4">
                <li class="mb-2">An administrator approves the photographer's WivorPhotos application.</li>
                <li class="mb-2">The photographer signs in and opens the payout setup card on their dashboard.</li>
                <li class="mb-2">The photographer selects <strong>Complete Payout Setup</strong>. WivorPhotos creates or reuses their connected Stripe account and redirects them to Stripe's secure onboarding pages.</li>
                <li class="mb-2">The photographer supplies identity, business, bank, and any other information requested directly by Stripe. WivorPhotos does not collect or store their bank account details.</li>
                <li class="mb-2">Stripe notifies WivorPhotos when the account changes. The photographer or an administrator can also request a fresh status check.</li>
                <li>When Stripe enables transfers and payouts and there are no currently due or past-due requirements, WivorPhotos marks the account <strong>Ready</strong>.</li>
            </ol>

            <div class="alert alert-info" role="alert">
                Photographers may publish photos and make sales before Stripe setup is ready. Their allocation is not
                discarded: WivorPhotos records a failed transfer obligation and retries it when Stripe later reports
                the account as Ready.
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Status</th>
                            <th scope="col">Meaning</th>
                            <th scope="col">Administrator response</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-secondary">Not started</span></td>
                            <td>No connected Stripe account has been created.</td>
                            <td>Ask the photographer to begin payout setup from their dashboard.</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-warning text-dark">Incomplete</span></td>
                            <td>Setup started, but Stripe still needs information.</td>
                            <td>Ask the photographer to continue the Stripe-hosted setup.</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-info text-dark">Under review</span></td>
                            <td>Stripe is verifying submitted information.</td>
                            <td>Usually wait. Use Refresh Stripe Status if the displayed status appears stale.</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-warning text-dark">Action required</span></td>
                            <td>Stripe needs corrected, updated, or additional information.</td>
                            <td>Ask the photographer to open payout setup and complete the requested action.</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">Ready</span></td>
                            <td>The approved photographer's account can receive WivorPhotos transfers.</td>
                            <td>No onboarding action is needed. Failed obligations are queued again on the transition to Ready.</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-danger">Restricted</span></td>
                            <td>Stripe has disabled or restricted the account.</td>
                            <td>Open the connected account in Stripe and ask the photographer to resolve the requirement.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" id="customer-payment">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">2. Customer Checkout and paid-order confirmation</h2>
            <p class="text-muted mb-0">A transfer is never created merely because a customer reached the Checkout page.</p>
        </div>
        <div class="card-body">
            <ol>
                <li class="mb-2">
                    <strong>The order is frozen locally.</strong> Before redirecting to Stripe, WivorPhotos creates a
                    pending order and stores each item's price, commission, and
                    <code>photographer_allocation_cents</code>. These stored integer-cent values are the financial
                    source of truth.
                </li>
                <li class="mb-2">
                    <strong>The customer pays on Stripe.</strong> Checkout is hosted by Stripe and the charge belongs
                    to the WivorPhotos platform account. WivorPhotos never relies on a browser-supplied payment status.
                </li>
                <li class="mb-2">
                    <strong>Stripe sends a signed webhook.</strong> WivorPhotos only fulfills
                    <code>checkout.session.completed</code> when Stripe reports the payment as paid and the session's
                    order metadata, currency, and total exactly match the pending local order.
                </li>
                <li class="mb-2">
                    <strong>The order becomes paid once.</strong> A database lock protects the pending-to-paid change.
                    WivorPhotos unlocks downloads, records Stripe's Payment Intent, updates photo sale counts, and
                    sends the receipt only for the successful transition.
                </li>
                <li>
                    <strong>Transfers begin after the order transaction commits.</strong> Payment confirmation is not
                    rolled back if a photographer transfer later fails.
                </li>
            </ol>

            <div class="alert alert-secondary mb-0" role="alert">
                <strong>Processing fees:</strong> the photographer allocation is not reduced by Stripe processing
                fees. WivorPhotos currently absorbs those fees. The existing commission and allocation calculation is
                used without recalculation during transfer processing.
            </div>

            <h3 class="h5 mt-4">How the order amount is divided</h3>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Amount</th>
                            <th scope="col">How it is determined</th>
                            <th scope="col">What happens to it</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Frozen unit price</td>
                            <td>The event's per-photo price after any valid promotional discount.</td>
                            <td>Stored on each order item and used to build the customer's total.</td>
                        </tr>
                        <tr>
                            <td>Wivor commission</td>
                            <td>The configured commission percentage is applied to the frozen unit price and rounded to whole cents.</td>
                            <td>Remains with WivorPhotos in the platform balance.</td>
                        </tr>
                        <tr>
                            <td>Photographer allocation</td>
                            <td>The frozen unit price minus the stored commission for that item.</td>
                            <td>Grouped by photographer and transferred without deducting Stripe processing fees.</td>
                        </tr>
                        <tr>
                            <td>Stripe processing fee</td>
                            <td>Calculated and charged by Stripe for the customer payment.</td>
                            <td>Absorbed by WivorPhotos under the current business rule.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" id="automatic-transfers">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">3. Automatic photographer transfers</h2>
            <p class="text-muted mb-0">The transfer unit is one paid order plus one photographer—not one photo.</p>
        </div>
        <div class="card-body">
            <ol>
                <li class="mb-2">WivorPhotos groups all paid order items by <code>photographer_id</code>.</li>
                <li class="mb-2">It adds that photographer's stored <code>photographer_allocation_cents</code> values.</li>
                <li class="mb-2">It creates one local transfer obligation for the order and photographer. The database forbids a second record for the same pair.</li>
                <li class="mb-2">It queues one job for that obligation, so each photographer can succeed or fail independently.</li>
                <li class="mb-2">The job retrieves Stripe's current connected-account state and confirms the order is still paid and the photographer is approved and Ready.</li>
                <li class="mb-2">The job creates a Stripe Connect transfer tied to the original customer charge, using the exact stored amount and currency.</li>
                <li>On success, WivorPhotos stores the Stripe Transfer ID, the success status, and the completion time. The photographer's related order items are then shown as paid out.</li>
            </ol>

            <h3 class="h5 mt-4">Example: one order with multiple photographers</h3>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Purchased item</th>
                            <th scope="col">Photographer</th>
                            <th scope="col">Stored photographer allocation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Photo 1</td>
                            <td>Photographer A</td>
                            <td>$8.00</td>
                        </tr>
                        <tr>
                            <td>Photo 2</td>
                            <td>Photographer A</td>
                            <td>$8.00</td>
                        </tr>
                        <tr>
                            <td>Photo 3</td>
                            <td>Photographer B</td>
                            <td>$8.00</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Stripe transfers created</th>
                            <td>One $16.00 transfer to A and one $8.00 transfer to B</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="text-muted mb-0">
                If A succeeds and B fails, A remains succeeded. Only B's obligation needs another attempt; A is not paid twice.
            </p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h2 class="h4 mb-1">Duplicate-payment protection</h2>
                    <p class="text-muted mb-0">Three layers protect every order-and-photographer payment.</p>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Database uniqueness</dt>
                        <dd class="text-muted">Only one transfer record can exist for the same order and photographer.</dd>

                        <dt>Queue protection</dt>
                        <dd class="text-muted">Overlapping jobs for the same transfer are prevented, and a job exits immediately when the record already succeeded.</dd>

                        <dt>Stripe idempotency</dt>
                        <dd class="text-muted mb-0">Every attempt uses the same deterministic key based on the Wivor order ID and photographer ID, so a retry represents the same Stripe operation.</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h2 class="h4 mb-1">Local transfer statuses</h2>
                    <p class="text-muted mb-0">The status describes the platform-to-connected-account transfer.</p>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt><span class="badge bg-secondary">pending</span></dt>
                        <dd class="text-muted">The obligation exists and is waiting for processing.</dd>

                        <dt><span class="badge bg-danger">failed</span></dt>
                        <dd class="text-muted">Money was not confirmed as transferred. The reason is kept for diagnosis and the allocation remains owed.</dd>

                        <dt><span class="badge bg-success">succeeded</span></dt>
                        <dd class="text-muted mb-0">Stripe returned a Transfer ID and WivorPhotos recorded the completion. This record must never be retried as a new payment.</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" id="failures">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">4. Failures, retries, and administrator response</h2>
            <p class="text-muted mb-0">A failed transfer does not change the customer's paid order or erase the amount owed.</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Situation</th>
                            <th scope="col">What WivorPhotos does</th>
                            <th scope="col">What the administrator should do</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Stripe setup not started or incomplete</td>
                            <td>Marks the obligation failed without calling the Transfer API.</td>
                            <td>Ask the photographer to finish payout setup. The Ready status transition queues the failed obligation again.</td>
                        </tr>
                        <tr>
                            <td>Connected account restricted or not eligible</td>
                            <td>Refreshes Stripe's state and leaves the obligation failed.</td>
                            <td>Open the photographer record, refresh Stripe status, and have the photographer resolve Stripe's requirements.</td>
                        </tr>
                        <tr>
                            <td>Temporary Stripe or network error</td>
                            <td>Records a safe error, logs technical identifiers, and retries the job up to three attempts with delays of 60 and 300 seconds.</td>
                            <td>Confirm the queue worker is running. If all attempts are exhausted, review application logs and Stripe before arranging a controlled retry.</td>
                        </tr>
                        <tr>
                            <td>Original Stripe charge is unavailable</td>
                            <td>Leaves the obligation failed because the transfer cannot be tied to its source transaction.</td>
                            <td>Reconcile the order's Payment Intent and latest charge in Stripe. Do not create an unrelated manual payment without confirming the history.</td>
                        </tr>
                        <tr>
                            <td>Photographer changed connected accounts</td>
                            <td>Refuses to send an existing obligation to a different account once a destination was recorded.</td>
                            <td>Investigate the account change and transfer history before any manual action.</td>
                        </tr>
                        <tr>
                            <td>One photographer succeeds and another fails</td>
                            <td>Keeps each result independently. Successful transfers are skipped on later jobs.</td>
                            <td>Work only on the failed photographer. Never recreate the successful transfer.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" id="reconciliation">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">5. How to investigate a payment or payout question</h2>
            <p class="text-muted mb-0">Work in this order so that the customer charge, photographer allocation, transfer, and bank payout are not confused.</p>
        </div>
        <div class="card-body">
            <ol class="mb-4">
                <li class="mb-2">
                    <strong>Find the Wivor order.</strong> Open <a href="{{ route('payments.index') }}">Photo Orders</a>
                    and note the order number, payment status, Stripe Checkout Session ID, and Payment Intent ID.
                </li>
                <li class="mb-2">
                    <strong>Confirm the customer charge in Stripe.</strong> Match the amount, currency, Payment Intent,
                    and charge. A paid local order should correspond to a successful Stripe payment.
                </li>
                <li class="mb-2">
                    <strong>Confirm the photographer's account.</strong> Open
                    <a href="{{ route('photographers.list') }}">Photographers</a>, select the photographer, and check
                    approval, onboarding status, connected account ID, transfer capability, bank payouts, and any
                    requirements. Use <strong>Refresh Stripe Status</strong> when necessary.
                </li>
                <li class="mb-2">
                    <strong>Separate transfer from payout.</strong> A Stripe Transfer ID proves that funds moved from
                    WivorPhotos to the connected Stripe account. It does not prove that Stripe has already deposited
                    the funds into the photographer's bank.
                </li>
                <li class="mb-2">
                    <strong>Check the connected Stripe account.</strong> Use Stripe's transfer and balance history to
                    locate the Transfer ID, destination account, source charge, amount, and arrival in the connected
                    balance. Then check that account's payout history for the bank deposit.
                </li>
                <li>
                    <strong>Escalate safely.</strong> If the transfer-level status or error is not visible in the current
                    admin screens, engineering or operations must review the local <code>photographer_transfers</code>
                    record, queue/failed-job state, and application logs. Never share Stripe secret keys, webhook
                    secrets, bank details, or full payment data in a support message.
                </li>
            </ol>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Identifier</th>
                            <th scope="col">What it identifies</th>
                            <th scope="col">Where it is useful</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Wivor order number</td>
                            <td>The customer's local WivorPhotos order</td>
                            <td>Admin order lookup and customer support</td>
                        </tr>
                        <tr>
                            <td>Checkout Session ID</td>
                            <td>The Stripe-hosted checkout visit</td>
                            <td>Matching Checkout to the local order</td>
                        </tr>
                        <tr>
                            <td>Payment Intent ID</td>
                            <td>The Stripe payment lifecycle</td>
                            <td>Charge, refund, and dispute reconciliation</td>
                        </tr>
                        <tr>
                            <td>Charge ID</td>
                            <td>The successful platform charge used as the transfer source</td>
                            <td>Tracing the customer funds into Connect transfers</td>
                        </tr>
                        <tr>
                            <td>Connected account ID</td>
                            <td>The photographer's Stripe account</td>
                            <td>Onboarding, capability, balance, and payout checks</td>
                        </tr>
                        <tr>
                            <td>Transfer ID</td>
                            <td>Money moved from WivorPhotos to the connected Stripe account</td>
                            <td>Confirming the photographer allocation was transferred</td>
                        </tr>
                        <tr>
                            <td>Payout ID</td>
                            <td>Money moved from a Stripe balance to a bank</td>
                            <td>Investigating bank arrival or payout failure</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h2 class="h4 mb-1">Refunds and disputes</h2>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">A full refund reported by Stripe marks the order refunded and revokes remaining downloads.</li>
                        <li class="mb-2">A new dispute reported by Stripe marks the order disputed and revokes remaining downloads.</li>
                        <li class="mb-2">If the order stops being paid before a queued transfer runs, the transfer job refuses to send it.</li>
                        <li class="mb-2"><strong>An already successful photographer transfer is not reversed automatically.</strong></li>
                        <li>Partial refunds and automatic transfer reversals are outside the current workflow and require manual review.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h2 class="h4 mb-1">Current boundaries</h2>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">The application does not manually trigger each photographer's bank payout.</li>
                        <li class="mb-2">There is no administrator button to create or retry a Connect transfer.</li>
                        <li class="mb-2">Transfer-level diagnostics are not yet displayed in the admin payment table.</li>
                        <li class="mb-2">Paid orders that existed before the transfer workflow are not automatically backfilled.</li>
                        <li>Commission amounts and photographer allocations remain exactly as stored on the order items.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" id="operations-checklist">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">Operations checklist</h2>
            <p class="text-muted mb-0">These controls must remain healthy for automatic transfers to operate.</p>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h3 class="h5">Stripe configuration</h3>
                    <ul class="mb-0">
                        <li class="mb-2">Platform API credentials belong to the correct test or live environment.</li>
                        <li class="mb-2">The platform webhook sends Checkout, refund, and dispute events to WivorPhotos.</li>
                        <li class="mb-2">The separate Connect webhook sends <code>account.updated</code> events.</li>
                        <li class="mb-2">Both webhook signing secrets match their Stripe endpoints and environment.</li>
                        <li>Wivor's platform payout schedule and bank account are maintained in Stripe.</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h3 class="h5">Application operations</h3>
                    <ul class="mb-0">
                        <li class="mb-2">A queue worker is continuously processing production jobs.</li>
                        <li class="mb-2">Failed jobs and payment-related application logs are monitored.</li>
                        <li class="mb-2">Administrators review restricted or action-required photographer accounts.</li>
                        <li class="mb-2">Refunds and disputes are checked for already-completed photographer transfers.</li>
                        <li>Development and automated tests use Stripe fakes or test mode—never live Transfer API calls.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">Plain-language glossary</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <tbody>
                        <tr>
                            <th scope="row">Stripe Checkout</th>
                            <td>The Stripe-hosted page where the customer enters payment information.</td>
                        </tr>
                        <tr>
                            <th scope="row">Platform account</th>
                            <td>WivorPhotos' Stripe account, where the customer charge is created.</td>
                        </tr>
                        <tr>
                            <th scope="row">Connected account</th>
                            <td>A photographer's Stripe account linked to WivorPhotos through Stripe Connect.</td>
                        </tr>
                        <tr>
                            <th scope="row">Transfer</th>
                            <td>Funds moved from the WivorPhotos Stripe balance to a photographer's connected Stripe balance.</td>
                        </tr>
                        <tr>
                            <th scope="row">Payout</th>
                            <td>Funds moved by Stripe from an account's Stripe balance to its external bank account.</td>
                        </tr>
                        <tr>
                            <th scope="row">Source transaction</th>
                            <td>The original customer charge that funds and anchors a photographer transfer.</td>
                        </tr>
                        <tr>
                            <th scope="row">Idempotency</th>
                            <td>A safety rule that makes repeated attempts refer to the same intended Stripe operation instead of creating a second payment.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
