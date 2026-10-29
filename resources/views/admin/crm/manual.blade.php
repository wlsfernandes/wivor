@extends('layouts.master')

@section('title', 'CRM Manual | WivorPhotos')

@section('content')
    @component('common-components.breadcrumb')
        @slot('pagetitle')
            CRM
        @endslot
        @slot('title')
            CRM manual
        @endslot
    @endcomponent

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-primary mb-2">Administrator guide</span>
                    <h1 class="h3 mb-2">How the CRM works</h1>
                    <p class="text-muted mb-0">
                        The CRM keeps one contact per email address, links each contact to the events they took part in,
                        and lets administrators email those people safely. This manual explains each step.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                    <a class="btn btn-primary" href="{{ route('admin.crm.contacts.index') }}">
                        <i class="fa fa-address-book me-1" aria-hidden="true"></i> Contacts
                    </a>
                    <a class="btn btn-outline-primary" href="{{ route('admin.crm.imports.index') }}">
                        <i class="fa fa-file-import me-1" aria-hidden="true"></i> CSV Imports
                    </a>
                    <a class="btn btn-outline-primary" href="{{ route('admin.crm.campaigns.index') }}">
                        <i class="fa fa-envelope me-1" aria-hidden="true"></i> Email Campaigns
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-warning border-0 shadow-sm" role="alert">
        <div class="d-flex">
            <i class="fa fa-exclamation-triangle font-size-20 me-3 mt-1" aria-hidden="true"></i>
            <div>
                <h2 class="h5 alert-heading">Campaign delivery is not active yet</h2>
                <p class="mb-0">
                    You can import contacts, write campaigns, preview them, send a test to yourself and schedule them.
                    Scheduled campaigns are <strong>not delivered</strong> until the delivery engine is installed in the
                    next release. Test emails are sent.
                </p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">The big picture</h2>
            <p class="text-muted mb-0">Four steps take a participant list from an organizer's spreadsheet to an inbox.</p>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="border rounded h-100 p-3">
                        <span class="badge rounded-pill bg-primary mb-3">1</span>
                        <h3 class="h5">Import participants</h3>
                        <p class="text-muted mb-0">Upload the organizer's CSV from the event page and choose which columns to keep.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="border rounded h-100 p-3">
                        <span class="badge rounded-pill bg-primary mb-3">2</span>
                        <h3 class="h5">Contacts are merged</h3>
                        <p class="text-muted mb-0">Each email address becomes one contact, linked to every event that person took part in.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="border rounded h-100 p-3">
                        <span class="badge rounded-pill bg-primary mb-3">3</span>
                        <h3 class="h5">Write a campaign</h3>
                        <p class="text-muted mb-0">Choose the email type and audience, write the message, preview it and send yourself a test.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="border rounded h-100 p-3">
                        <span class="badge rounded-pill bg-primary mb-3">4</span>
                        <h3 class="h5">Schedule</h3>
                        <p class="text-muted mb-0">The system fixes the recipient list, leaving out anyone who is not allowed to receive the email.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">Import participants</h2>
            <p class="text-muted mb-0">Start from the event: <strong>Media</strong> page or <strong>Edit event</strong> page, then click <strong>Import Participants</strong>.</p>
        </div>
        <div class="card-body">
            <ol class="mb-4">
                <li class="mb-2">
                    <strong>Upload the CSV.</strong> Up to 5 MB. Columns may be separated by commas, semicolons, tabs or
                    pipes. The first row must contain the column names. Excel exports with accents are handled.
                </li>
                <li class="mb-2">
                    <strong>Say where the data came from.</strong> Enter the data source (for example "RunSignup export")
                    and the organizer. This is stored with every import.
                </li>
                <li class="mb-2">
                    <strong>Record consent honestly.</strong> Leave consent as <em>Unknown</em> unless you hold written
                    evidence, such as the registration form wording people agreed to. Choosing <em>Granted</em> requires
                    that evidence, what it covers (event emails or marketing) and who it was given to (this event only or WiVor).
                </li>
                <li class="mb-2">
                    <strong>Map the columns.</strong> Choose the email column (required) and any of first name, last name,
                    full name, phone, bib number and division. Up to 10 extra columns can be kept. Columns you do not map
                    are ignored. Save the mapping as a <em>preset</em> to reuse it for the same organizer next time.
                </li>
                <li class="mb-2">
                    <strong>Check the preview.</strong> Nothing is imported yet. Review the first 20 rows and the counts:
                    contacts to import, already in the CRM, duplicate rows and rows with errors.
                </li>
                <li class="mb-2">
                    <strong>Start the import.</strong> It runs in the background. The status page refreshes itself and
                    shows the result. If it fails, use <strong>Retry import</strong>.
                </li>
                <li>
                    <strong>Download the error report</strong> to see which rows were skipped and why (missing or invalid
                    email, duplicate row, value too long).
                </li>
            </ol>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="border rounded h-100 p-3">
                        <h3 class="h6">Importing again is safe</h3>
                        <p class="text-muted mb-0">
                            Re-importing the same file never creates duplicate contacts or duplicate event links. A newer
                            bib or division replaces the old one. Existing names and phones are never overwritten; the
                            import only fills in empty ones.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded h-100 p-3">
                        <h3 class="h6">Files are deleted after 30 days</h3>
                        <p class="text-muted mb-0">
                            Uploaded CSVs and error reports are private and are deleted 30 days after the import finishes.
                            An import never started within 30 days is marked failed and its file deleted.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">Contacts</h2>
            <p class="text-muted mb-0">One person, one contact, however many events they run.</p>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li class="mb-2"><strong>All Contacts</strong> lets you search by email or name and filter by event.</li>
                <li class="mb-2">A contact's page shows every event they took part in (with bib and division), their consent history and any suppressions.</li>
                <li class="mb-2">Each event's participant list is available from the event page (<strong>Participants</strong>).</li>
                <li>Photo buyers and customer accounts can be added by a developer with the <code>crm:import-buyers</code> command. They are added without any consent.</li>
            </ul>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">Who can receive which email</h2>
            <p class="text-muted mb-0">The system applies these rules automatically. They cannot be overridden from the admin panel.</p>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Email type</th><th>Who it can go to</th><th>Consent needed</th><th>Blocked when</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Event / photo notification</strong><br><span class="text-muted">e.g. "Your race photos are live"</span></td>
                        <td>Participants of the selected events only</td>
                        <td>No — unknown consent is allowed</td>
                        <td>The person unsubscribed, bounced, complained or was suppressed by an admin</td>
                    </tr>
                    <tr>
                        <td><strong>Promotion</strong> or <strong>Newsletter</strong></td>
                        <td>Participants of selected events, or the whole directory</td>
                        <td>Yes — recorded marketing consent for WiVor or for one of the selected events</td>
                        <td>No recorded consent, consent revoked, or any of the blocks above</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">
                Someone in several selected events receives <strong>one</strong> copy. Importing a list never creates
                marketing consent on its own, and it never re-grants consent to someone who opted out.
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">Write and schedule a campaign</h2>
            <p class="text-muted mb-0">Start from <strong>Email Campaigns</strong>, a template, or an event's <strong>Email Participants</strong> button.</p>
        </div>
        <div class="card-body">
            <ol class="mb-4">
                <li class="mb-2"><strong>Choose the type and audience.</strong> Pick the email type, then the events (or the whole directory for promotions and newsletters).</li>
                <li class="mb-2"><strong>Write the message.</strong> Use the visual editor or the HTML tab. Scripts, forms and unsafe links are removed when you save. Images must use <code>https://</code> addresses. Add an optional button with its text and link.</li>
                <li class="mb-2"><strong>Preview.</strong> See the email on mobile and desktop, plus the plain-text version, filled with sample values from your account.</li>
                <li class="mb-2"><strong>Send a test.</strong> <em>Send test to me</em> emails only you, with "[TEST]" in the subject.</li>
                <li class="mb-2"><strong>Check the counts.</strong> The campaign page shows how many people are eligible and how many are not.</li>
                <li><strong>Schedule.</strong> Pick the send time in <strong>Eastern time</strong>. The recipient list is fixed at that moment. To change anything, use <em>Move back to draft</em>, edit, and schedule again. <em>Cancel campaign</em> cannot be undone.</li>
            </ol>

            <h3 class="h5">Personalization placeholders</h3>
            <p class="text-muted">Type these in the subject, message, button link or plain-text version. Anything else in double braces is rejected.</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-3">
                    <thead class="table-light"><tr><th>Placeholder</th><th>Becomes</th><th>Single event only</th></tr></thead>
                    <tbody>
                        <tr><td><code>@{{first_name}}</code></td><td>First name, or "there" if unknown</td><td></td></tr>
                        <tr><td><code>@{{last_name}}</code></td><td>Last name</td><td></td></tr>
                        <tr><td><code>@{{event_name}}</code></td><td>Event title</td><td>Yes</td></tr>
                        <tr><td><code>@{{event_date}}</code></td><td>Event date</td><td>Yes</td></tr>
                        <tr><td><code>@{{event_url}}</code></td><td>Link to the event page</td><td>Yes</td></tr>
                        <tr><td><code>@{{photo_gallery_url}}</code></td><td>Link to the event's photo gallery</td><td>Yes</td></tr>
                        <tr><td><code>@{{bib_number}}</code></td><td>The person's bib number for that event</td><td>Yes</td></tr>
                        <tr><td><code>@{{unsubscribe_url}}</code></td><td>The person's unsubscribe link</td><td></td></tr>
                    </tbody>
                </table>
            </div>
            <p class="text-muted mb-0">
                "Single event only" placeholders are rejected when a campaign targets several events or the whole
                directory, because the system could not know which event to use. Every email automatically includes
                the reason the person receives it, WiVor's mailing address and an unsubscribe link.
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h2 class="h4 mb-1">Common questions</h2>
        </div>
        <div class="card-body">
            <h3 class="h6">Why is the eligible count lower than the participant count?</h3>
            <p class="text-muted">People who opted out, bounced or complained are left out. For promotions and newsletters, everyone without recorded marketing consent is also left out.</p>

            <h3 class="h6">The Schedule button is disabled. Why?</h3>
            <p class="text-muted">Either no one in the audience is eligible, or the message has a problem shown in yellow at the top of the campaign page (for example an event placeholder with several events selected).</p>

            <h3 class="h6">Can I edit a scheduled campaign?</h3>
            <p class="text-muted">No. Use <em>Move back to draft</em>, make the change, preview, test and schedule again.</p>

            <h3 class="h6">An import shows rows with errors.</h3>
            <p class="text-muted mb-0">Download the error report from the import page, fix those rows in the spreadsheet, and import the file again. Rows already imported are not duplicated.</p>
        </div>
    </div>
@endsection
