@extends('layouts.master')
@section('title', 'Map Columns — '.$import->original_filename)
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.imports.show', $import) }}">← Import #{{ $import->id }}</a>
        <h1 class="h3 mt-2 mb-1">Map columns</h1>
        <p class="text-muted">{{ $import->original_filename }} for {{ $import->event->title }}. Unmapped columns are ignored.</p>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($presets->isNotEmpty())
            <form method="GET" action="{{ route('admin.crm.imports.mapping.edit', $import) }}" class="d-flex gap-2 mb-3">
                <select name="preset" class="form-select w-auto" aria-label="Mapping preset">
                    @foreach ($presets as $preset)
                        <option value="{{ $preset->id }}" @selected((int) request('preset') === $preset->id)>{{ $preset->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-secondary">Load preset</button>
            </form>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.crm.imports.mapping.update', $import) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="mapping_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <select id="mapping_email" name="mapping[email]" class="form-select" required>
                                <option value="">Choose a column</option>
                                @foreach ($headers as $header)
                                    <option value="{{ $header }}" @selected(old('mapping.email', $mapping['email'] ?? null) === $header)>{{ $header }}</option>
                                @endforeach
                            </select>
                        </div>
                        @foreach ($fields as $field => $rules)
                            <div class="col-md-4 mb-3">
                                <label for="mapping_{{ $field }}" class="form-label">{{ $rules['label'] }}</label>
                                <select id="mapping_{{ $field }}" name="mapping[{{ $field }}]" class="form-select">
                                    <option value="">Do not import</option>
                                    @foreach ($headers as $header)
                                        <option value="{{ $header }}" @selected(old('mapping.'.$field, $mapping[$field] ?? null) === $header)>{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label for="mapping_extra_fields" class="form-label">Extra fields to keep (up to 10)</label>
                        <select id="mapping_extra_fields" name="mapping[extra_fields][]" class="form-select" multiple size="6">
                            @foreach ($headers as $header)
                                <option value="{{ $header }}" @selected(in_array($header, old('mapping.extra_fields', $mapping['extra_fields'] ?? []), true))>{{ $header }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Stored with this event's participant record. Do not select sensitive data you do not need.</div>
                    </div>

                    <div class="mb-3">
                        <label for="preset_name" class="form-label">Save as preset (optional)</label>
                        <input type="text" id="preset_name" name="preset_name" class="form-control" maxlength="100" value="{{ old('preset_name') }}"
                            placeholder="e.g. RunSignup export">
                    </div>

                    <button type="submit" class="btn btn-primary">Preview import</button>
                </form>
            </div>
        </div>
    </div>
@endsection
