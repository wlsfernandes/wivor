{{-- Shared email content fields; $content is a CrmCampaign or CrmTemplate. Pair with admin.crm._editor-script. --}}
<link href="{{ asset('assets/libs/quill/quill.min.css') }}" rel="stylesheet" type="text/css">

<div class="mb-3">
    <label for="subject" class="form-label">Subject</label>
    <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror"
        value="{{ old('subject', $content->subject) }}" maxlength="255" required>
</div>

<div class="mb-3">
    <label for="preview_text" class="form-label">Preview text (optional)</label>
    <input type="text" id="preview_text" name="preview_text" class="form-control @error('preview_text') is-invalid @enderror"
        value="{{ old('preview_text', $content->preview_text) }}" maxlength="255">
    <div class="form-text">Shown next to the subject in most inboxes.</div>
</div>

<div class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <label for="body_html" class="form-label mb-0">Message</label>
        <div class="btn-group btn-group-sm" role="group" aria-label="Editor mode">
            <button type="button" class="btn btn-outline-secondary active" data-editor-mode="visual">Visual</button>
            <button type="button" class="btn btn-outline-secondary" data-editor-mode="html">HTML</button>
        </div>
    </div>
    <div id="body-editor" style="min-height: 260px; background: #fff;"></div>
    <textarea id="body_html" name="body_html" rows="14" class="form-control font-monospace d-none @error('body_html') is-invalid @enderror">{{ old('body_html', $content->body_html) }}</textarea>
    <div class="form-text">
        Scripts, styles, forms and unsafe links are removed when you save. Images must use https:// addresses.
        Placeholders: @foreach ($placeholders as $placeholder)<code>&#123;&#123;{{ $placeholder }}&#125;&#125;</code>@if (! $loop->last), @endif @endforeach.
        Event placeholders work only when one event is selected.
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="cta_label" class="form-label">Button text (optional)</label>
        <input type="text" id="cta_label" name="cta_label" class="form-control @error('cta_label') is-invalid @enderror"
            value="{{ old('cta_label', $content->cta_label) }}" maxlength="60" placeholder="See your photos">
    </div>
    <div class="col-md-8 mb-3">
        <label for="cta_url" class="form-label">Button link</label>
        <input type="text" id="cta_url" name="cta_url" class="form-control @error('cta_url') is-invalid @enderror"
            value="{{ old('cta_url', $content->cta_url) }}" maxlength="2048" placeholder="https://… or @{{photo_gallery_url}}">
    </div>
</div>

<div class="mb-3">
    <label for="text_body" class="form-label">Plain-text version (optional)</label>
    <textarea id="text_body" name="text_body" rows="5" class="form-control @error('text_body') is-invalid @enderror">{{ old('text_body', $content->text_body) }}</textarea>
    <div class="form-text">Leave empty to generate it from the message. Every email includes a plain-text version.</div>
</div>
