@extends('layouts.master')
@section('title', 'Preview Import — '.$import->original_filename)
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.imports.mapping.edit', $import) }}">← Change mapping</a>
        <h1 class="h3 mt-2 mb-1">Preview import</h1>
        <p class="text-muted">{{ $import->original_filename }} for {{ $import->event->title }}. Nothing has been imported yet.</p>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="row mb-3">
            @foreach ([
                'rows' => 'Rows in file',
                'valid' => 'Contacts to import',
                'existingContacts' => 'Already in CRM',
                'duplicates' => 'Duplicate rows (skipped)',
                'invalid' => 'Rows with errors',
            ] as $countKey => $label)
                <div class="col">
                    <div class="card"><div class="card-body">
                        <p class="text-muted mb-1">{{ $label }}</p>
                        <h2 class="h4 mb-0">{{ number_format($counts[$countKey]) }}</h2>
                    </div></div>
                </div>
            @endforeach
        </div>

        <div class="card mb-3">
            <div class="card-header"><h2 class="h5 mb-0">First {{ count($sampleRows) }} rows</h2></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Line</th><th>Email</th><th>First name</th><th>Last name</th><th>Full name</th><th>Bib</th><th>Division</th><th>Result</th></tr></thead>
                    <tbody>
                        @foreach ($sampleRows as $row)
                            <tr>
                                <td>{{ $row->lineNumber }}</td>
                                @if ($row->error === null)
                                    <td>{{ $row->fields['email_original'] }}</td>
                                    <td>{{ $row->fields['first_name'] }}</td>
                                    <td>{{ $row->fields['last_name'] }}</td>
                                    <td>{{ $row->fields['full_name'] }}</td>
                                    <td>{{ $row->fields['bib_number'] }}</td>
                                    <td>{{ $row->fields['division'] }}</td>
                                    <td><span class="badge bg-success">OK</span></td>
                                @else
                                    <td colspan="6" class="text-muted">—</td>
                                    <td><span class="text-danger">{{ $row->error }}</span></td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.crm.imports.start', $import) }}"
            onsubmit="return confirm(@js('Import '.number_format($counts['valid']).' contacts into '.$import->event->title.'?'))">
            @csrf
            <button type="submit" class="btn btn-success" @disabled($counts['valid'] === 0)>Start import of {{ number_format($counts['valid']) }} contacts</button>
        </form>
    </div>
@endsection
