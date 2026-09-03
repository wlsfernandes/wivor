@extends('layouts.app-sidebar')

@section('title', 'Upload photos — '.$event->title)

@section('content')
<div class="container-fluid" id="photo-uploader"
    data-create-url="{{ route('photographer.uploads.batches.store', $event) }}"
    data-status-url="{{ route('photographer.uploads.status', $event) }}"
    data-complete-template="{{ route('photographer.uploads.complete', [$event, '__PHOTO__']) }}"
    data-retry-template="{{ route('photographer.uploads.retry-url', [$event, '__PHOTO__']) }}"
    data-delete-template="{{ route('photographer.uploads.destroy', [$event, '__PHOTO__']) }}"
    data-max-files="{{ $rules['max_batch_size'] }}" data-max-bytes="{{ $rules['max_file_bytes'] }}"
    data-upload-open="{{ $uploadOpen ? '1' : '0' }}">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('events.index') }}" class="small text-decoration-none">← Return to My Events</a>
            <h1 class="h3 mt-2 mb-1">Upload photos</h1>
            <p class="mb-0 fw-semibold">{{ $event->title }}</p>
            <p class="text-muted">{{ $event->date_label }} · {{ $event->venue_name ? $event->venue_name.' · ' : '' }}{{ $event->location_label }}</p>
        </div>
        <div class="text-md-end">
            <span class="badge bg-success">Assignment {{ ucfirst($assignment->status) }}</span>
            <div class="small mt-2"><strong>Gallery:</strong> {{ $galleryStatus }}</div>
            <div class="small mt-2"><strong>Upload deadline:</strong><br>{{ $deadline->timezone($event->timezone)->format('M j, Y g:i A T') }}</div>
        </div>
    </div>
    <p class="small text-muted mb-4">Queued: {{ $counts['queued'] ?? 0 }} · Uploading: {{ $counts['uploading'] ?? 0 }} · Processing: {{ $counts['processing'] ?? 0 }} · Ready: {{ $counts['ready'] ?? 0 }} · Rejected: {{ $counts['rejected'] ?? 0 }} · Published: {{ $counts['published'] ?? 0 }}</p>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    @unless ($uploadOpen)
        <div class="alert alert-warning">Upload deadline passed. Contact WivorPhotos if an extension is required.</div>
    @endunless

    <div class="row g-3 mb-4">
        @foreach ([
            ['Accepted', $acceptedCount.' / '.$rules['max_event_photos'], 'primary'],
            ['Remaining', $remainingCount, 'secondary'],
            ['Ready', $counts['ready'] ?? 0, 'success'],
            ['Rejected', $counts['rejected'] ?? 0, 'danger'],
            ['Published', $counts['published'] ?? 0, 'info'],
        ] as [$label, $value, $color])
            <div class="col-6 col-lg"><div class="card h-100"><div class="card-body py-3"><div class="small text-muted">{{ $label }}</div><div class="h4 mb-0 text-{{ $color }}" data-count="{{ strtolower($label) }}">{{ $value }}</div></div></div></div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Photo requirements</h2></div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <ul class="mb-lg-0">
                        <li>Accepted format: <strong>.jpg or .jpeg only</strong>.</li>
                        <li>Maximum file size: <strong>40 MB per photo</strong>.</li>
                        <li>Longest side must be at least 2,400 pixels.</li>
                        <li>Neither side may exceed 12,000 pixels.</li>
                        <li>RGB/sRGB only; CMYK is not accepted.</li>
                        <li>No RAW, TIFF, PSD, PNG, HEIC, GIF, or video.</li>
                        <li>No photographer watermark, logo, border, or marketplace branding.</li>
                        <li>Corrupt and exact duplicate photos are rejected.</li>
                        <li>Up to 500 selections per batch and 5,000 accepted photos for this event.</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h3 class="h6">Photographer notice</h3>
                    <ul>
                        <li>Upload only photographs you created or are legally authorized to sell.</li>
                        <li>Keep your own backup of every original.</li>
                        <li>WivorPhotos is a sales platform, not permanent backup storage.</li>
                        <li>Unsold media is normally removed 60 days after gallery publication.</li>
                        <li>WivorPhotos applies its own watermark to previews and thumbnails.</li>
                    </ul>
                    @if ($assignment->rights_confirmed_at)
                        <div class="alert alert-success py-2 mb-0">Rights and backup confirmed {{ $assignment->rights_confirmed_at->format('M j, Y') }}.</div>
                        <input type="hidden" id="rights-confirmed" value="1">
                    @else
                        <div class="form-check border rounded p-3 ps-5">
                            <input class="form-check-input" id="rights-confirmed" type="checkbox" value="1">
                            <label class="form-check-label" for="rights-confirmed">I confirm that I own or control the rights to these photographs, may offer them for sale, and have kept my own backup.</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <input id="photo-input" type="file" accept=".jpg,.jpeg,image/jpeg" multiple hidden @disabled(! $uploadOpen)>
            <button type="button" id="drop-zone" class="btn w-100 border border-2 rounded p-5 text-center bg-light" @disabled(! $uploadOpen)>
                <span class="h5 d-block">Drop JPEG photos here</span>
                <span class="text-muted">or click to choose files</span>
            </button>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button class="btn btn-primary" type="button" id="upload-button" disabled>Add Photos</button>
                <button class="btn btn-outline-warning" type="button" id="retry-button" disabled>Retry Failed</button>
                <span class="ms-auto align-self-center"><span id="overall-label">No batch selected</span></span>
            </div>
            <div class="progress mt-2" style="height: 8px"><div id="overall-progress" class="progress-bar" style="width:0%"></div></div>
            <div id="client-error" class="alert alert-danger mt-3 d-none"></div>
            <div id="upload-rows" class="mt-3"></div>
        </div>
    </div>

    <form id="publish-photos-form" method="POST" action="{{ route('photographer.uploads.publish', $event) }}">
        @csrf
    </form>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <h2 class="h4 mb-0 me-auto">Review photos</h2>
        <div class="form-check"><input class="form-check-input" id="select-all-ready" type="checkbox" @checked(($counts['ready'] ?? 0) > 0) @disabled(($counts['ready'] ?? 0) === 0)><label class="form-check-label" for="select-all-ready">Select all ready</label></div>
        <button class="btn btn-success" id="publish-ready-button" type="submit" form="publish-photos-form" @disabled(($counts['ready'] ?? 0) === 0)>Publish Ready Photos</button>
    </div>
    <p class="small text-muted mb-3">Add a natural title, useful alt text, and a caption. Identify people only when you are authorized to publish their names; minors require special care.</p>
        <div class="row g-3" id="review-grid">
            @forelse ($photos as $photo)
                <div class="col-6 col-md-4 col-xl-3" data-server-photo="{{ $photo->uuid }}">
                    <div class="card h-100">
                        @if (in_array($photo->status, ['ready', 'published']))
                            <img src="{{ route('photographer.uploads.preview', [$event, $photo]) }}" class="card-img-top" style="aspect-ratio:1/1;object-fit:cover" alt="">
                        @endif
                        <div class="card-body p-3">
                            <div class="text-truncate small" title="{{ $photo->original_filename }}">{{ $photo->original_filename }}</div>
                            <span class="badge mt-2 {{ $photo->status === 'rejected' ? 'bg-danger' : ($photo->status === 'ready' ? 'bg-success' : 'bg-secondary') }}">{{ ucfirst($photo->status) }}</span>
                            @if ($photo->rejection_reason)<p class="small text-danger mt-2 mb-0">{{ $photo->rejection_reason }}</p>@endif
                            @if ($photo->status === 'ready')
                                <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="photo_ids[]" value="{{ $photo->uuid }}" form="publish-photos-form" checked><label class="form-check-label small">Publish</label></div>
                            @endif

                            @if (in_array($photo->status, ['ready', 'published']))
                                <details class="mt-3">
                                    <summary class="small fw-semibold">SEO and people</summary>
                                    <form method="POST" action="{{ route('photographer.uploads.metadata', [$event, $photo]) }}" class="mt-3">
                                        @csrf
                                        @method('PATCH')
                                        <label class="form-label small" for="title-{{ $photo->uuid }}">Photo title</label>
                                        <input class="form-control form-control-sm mb-2" id="title-{{ $photo->uuid }}" name="title" maxlength="80" value="{{ $photo->title }}" placeholder="Runner crossing the finish line">

                                        <label class="form-label small" for="alt-{{ $photo->uuid }}">Alt text</label>
                                        <textarea class="form-control form-control-sm mb-2" id="alt-{{ $photo->uuid }}" name="alt_text" maxlength="250" rows="2" placeholder="Describe what is visible">{{ $photo->alt_text }}</textarea>

                                        <label class="form-label small" for="caption-{{ $photo->uuid }}">Caption</label>
                                        <textarea class="form-control form-control-sm mb-2" id="caption-{{ $photo->uuid }}" name="caption" maxlength="1000" rows="2">{{ $photo->caption }}</textarea>

                                        <label class="form-label small" for="copyright-{{ $photo->uuid }}">Copyright notice</label>
                                        <input class="form-control form-control-sm mb-2" id="copyright-{{ $photo->uuid }}" name="copyright_notice" maxlength="255" value="{{ $photo->copyright_notice }}" placeholder="© 2026 Copyright owner">

                                        <label class="form-label small" for="people-{{ $photo->uuid }}">People shown</label>
                                        <textarea class="form-control form-control-sm mb-2" id="people-{{ $photo->uuid }}" name="people" maxlength="2000" rows="2" placeholder="One name per line">{{ collect($photo->people)->join("\n") }}</textarea>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" id="people-confirmed-{{ $photo->uuid }}" name="people_publication_confirmed" type="checkbox" value="1" @checked($photo->people_publication_confirmed_at)>
                                            <label class="form-check-label small" for="people-confirmed-{{ $photo->uuid }}">I confirm authorization to publish these names, including guardian authorization for any minor.</label>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Save SEO details</button>
                                    </form>
                                </details>
                            @endif

                            @if (in_array($photo->status, ['queued', 'uploading', 'ready', 'rejected']))
                                <form method="POST" action="{{ route('photographer.uploads.destroy', [$event, $photo]) }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Remove this unpublished photo?')">Remove</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted" id="empty-state">Completed uploads will appear here.</div>
            @endforelse
        </div>
        <div class="mt-4">{{ $photos->links() }}</div>

    <div class="card mt-4 border-info"><div class="card-body">
        <h2 class="h6">Gallery and media expiration</h2>
        @if ($event->gallery_published_at)
            <p class="mb-1"><strong>Gallery published:</strong> {{ $event->gallery_published_at->timezone($event->timezone)->format('M j, Y g:i A T') }}</p>
            <p class="mb-1"><strong>Sales close / unsold media deletion:</strong> {{ $event->sales_close_at->timezone($event->timezone)->format('M j, Y g:i A T') }} ({{ now()->diffInDays($event->sales_close_at, false) }} days remaining)</p>
            <p class="mb-1"><strong>Photos scheduled for deletion:</strong> {{ $scheduledDeletionCount }}</p>
            <p class="mb-0">Sold originals may remain longer for authorized customer downloads.</p>
        @else
            <p class="mb-0"><strong>Expected expiration:</strong> 60 days after the first gallery publication. No publication date has been set yet.</p>
        @endif
    </div></div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('photo-uploader');
    const selectAll = document.getElementById('select-all-ready');
    selectAll?.addEventListener('change', () => document.querySelectorAll('input[name="photo_ids[]"]').forEach(input => input.checked = selectAll.checked));
    const input = document.getElementById('photo-input');
    if (!root || !input) return;
    const uploadOpen = root.dataset.uploadOpen === '1';
    const zone = document.getElementById('drop-zone');
    const uploadButton = document.getElementById('upload-button');
    const retryButton = document.getElementById('retry-button');
    const rows = document.getElementById('upload-rows');
    const errorBox = document.getElementById('client-error');
    const overall = document.getElementById('overall-progress');
    const overallLabel = document.getElementById('overall-label');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let items = [];

    const showError = message => { errorBox.textContent = message; errorBox.classList.remove('d-none'); };
    const clearError = () => errorBox.classList.add('d-none');
    const escapeHtml = value => { const node = document.createElement('div'); node.textContent = value; return node.innerHTML; };
    const refreshOverall = () => {
        const total = items.length || 1;
        const progress = Math.round(items.reduce((sum, item) => sum + item.progress, 0) / total);
        overall.style.width = progress + '%'; overallLabel.textContent = items.length ? `${progress}% overall · ${items.filter(i => i.progress === 100).length} transferred` : 'No batch selected';
        retryButton.disabled = !uploadOpen || !items.some(item => item.status === 'failed');
    };
    const render = () => {
        rows.innerHTML = items.map((item, index) => `<div class="border rounded p-2 mb-2" data-index="${index}"><div class="d-flex gap-2 align-items-center"><span class="text-truncate flex-grow-1">${escapeHtml(item.file.name)}</span><span class="small status">${escapeHtml(item.label)}</span>${item.status === 'queued' ? `<button type="button" class="btn btn-sm btn-link text-danger remove-file" data-index="${index}">Remove</button>` : ''}</div><div class="progress mt-1" style="height:5px"><div class="progress-bar ${item.status === 'failed' ? 'bg-danger' : ''}" style="width:${item.progress}%"></div></div>${item.error ? `<div class="small text-danger mt-1">${escapeHtml(item.error)}</div>` : ''}</div>`).join('');
        uploadButton.disabled = !uploadOpen || !items.some(item => item.status === 'queued'); refreshOverall();
    };
    const selectFiles = fileList => {
        if (!uploadOpen) return;
        clearError(); const files = Array.from(fileList);
        if (files.length > Number(root.dataset.maxFiles)) return showError(`Select no more than ${root.dataset.maxFiles} photos per batch.`);
        const invalid = files.find(file => !/\.jpe?g$/i.test(file.name) || (file.type && file.type !== 'image/jpeg'));
        if (invalid) return showError(`${invalid.name}: Unsupported format. Upload a JPG or JPEG file.`);
        const oversized = files.find(file => file.size > Number(root.dataset.maxBytes));
        if (oversized) return showError(`${oversized.name}: File is larger than 40 MB. Export a smaller JPEG and try again.`);
        items = files.map(file => ({file, status:'queued', label:'Queued', progress:0, id:null, error:null})); render();
    };
    zone.addEventListener('click', () => input.click());
    input.addEventListener('change', () => selectFiles(input.files));
    ['dragenter','dragover'].forEach(type => zone.addEventListener(type, event => { event.preventDefault(); zone.classList.add('border-primary'); }));
    ['dragleave','drop'].forEach(type => zone.addEventListener(type, event => { event.preventDefault(); zone.classList.remove('border-primary'); }));
    zone.addEventListener('drop', event => selectFiles(event.dataTransfer.files));
    rows.addEventListener('click', event => { const button = event.target.closest('.remove-file'); if (button) { items.splice(Number(button.dataset.index), 1); render(); } });

    const jsonRequest = async (url, options = {}) => {
        const response = await fetch(url, {headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json', ...(options.headers || {})}, ...options});
        const body = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body.message || Object.values(body.errors || {})[0]?.[0] || 'Request failed. Please try again.');
        return body;
    };
    const putFile = (item, upload) => new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest(); xhr.open('PUT', upload.url);
        Object.entries(upload.headers || {}).forEach(([key,value]) => xhr.setRequestHeader(key,value));
        if (!Object.keys(upload.headers || {}).some(key => key.toLowerCase() === 'content-type')) xhr.setRequestHeader('Content-Type','image/jpeg');
        xhr.upload.onprogress = event => { if (event.lengthComputable) { item.progress = Math.round(event.loaded / event.total * 90); item.label = `Uploading ${Math.round(event.loaded / event.total * 100)}%`; render(); } };
        xhr.onload = () => xhr.status >= 200 && xhr.status < 300 ? resolve() : reject(new Error('Upload interrupted. Retry this file.'));
        xhr.onerror = () => reject(new Error('Upload interrupted. Check your connection and retry.')); xhr.send(item.file);
    });
    const uploadOne = async (item, upload) => {
        try {
            if (upload.error) throw new Error(upload.error);
            item.id = upload.id; item.status = 'uploading'; item.label = 'Uploading'; render();
            await putFile(item, upload);
            item.label = 'Processing'; item.progress = 95; render();
            await jsonRequest(root.dataset.completeTemplate.replace('__PHOTO__', item.id), {method:'POST', body:'{}'});
            item.status = 'processing'; item.label = 'Processing'; item.progress = 100; render();
        } catch (error) { item.status = 'failed'; item.label = 'Queued'; item.error = error.message; render(); }
    };
    const pollUntilSettled = async () => {
        for (let attempt = 0; attempt < 30; attempt++) {
            const result = await jsonRequest(root.dataset.statusUrl);
            let processing = false;
            items.forEach(item => {
                const server = result.photos.find(photo => photo.id === item.id);
                if (!server || item.status === 'failed') return;
                item.status = server.status; item.label = server.status.charAt(0).toUpperCase() + server.status.slice(1);
                item.error = server.reason; processing ||= ['queued','uploading','processing'].includes(server.status);
            });
            render();
            if (!processing) return;
            await new Promise(resolve => setTimeout(resolve, 2000));
        }
    };
    uploadButton.addEventListener('click', async () => {
        clearError();
        if (!document.getElementById('rights-confirmed').checked && document.getElementById('rights-confirmed').type === 'checkbox') return showError('Confirm the rights and backup notice before uploading.');
        uploadButton.disabled = true;
        try {
            const body = {rights_confirmed:true, files:items.filter(i => i.status === 'queued').map(i => ({name:i.file.name,size:i.file.size,type:i.file.type || 'image/jpeg'}))};
            const batch = await jsonRequest(root.dataset.createUrl, {method:'POST', body:JSON.stringify(body)});
            const queued = items.filter(i => i.status === 'queued');
            await Promise.all(queued.map((item,index) => uploadOne(item,batch.photos[index])));
            await pollUntilSettled(); window.location.reload();
        } catch (error) { showError(error.message); uploadButton.disabled = false; }
    });
    retryButton.addEventListener('click', async () => {
        for (const item of items.filter(i => i.status === 'failed' && i.id)) {
            item.error = null; item.progress = 0;
            try { await uploadOne(item, await jsonRequest(root.dataset.retryTemplate.replace('__PHOTO__', item.id), {method:'POST',body:'{}'})); } catch (error) { item.error = error.message; }
        }
    });
});
</script>
@endsection
