@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label for="code" class="form-label">Promo Code</label>
    <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
        value="{{ old('code', $promoCode->code ?? '') }}" maxlength="50" required>
    @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="discount_percent" class="form-label">Discount %</label>
    <input type="number" id="discount_percent" name="discount_percent"
        class="form-control @error('discount_percent') is-invalid @enderror"
        value="{{ old('discount_percent', $promoCode->discount_percent ?? '') }}" min="1" max="100"
        required>
    @error('discount_percent')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="expires_at" class="form-label">Expiration Date</label>
    <input type="date" id="expires_at" name="expires_at"
        class="form-control @error('expires_at') is-invalid @enderror"
        value="{{ old('expires_at', isset($promoCode) ? $promoCode->expires_at->format('Y-m-d') : '') }}" required>
    @error('expires_at')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-check mb-3">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
        @checked((bool) old('is_active', $promoCode->is_active ?? true))>
    <label for="is_active" class="form-check-label">Active</label>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
    <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-secondary waves-effect">Cancel</a>
</div>
