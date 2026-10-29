@extends('layouts.master')
@section('title', 'Email Templates')
@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h3 mb-0">Email templates</h1>
            <a href="{{ route('admin.crm.templates.create') }}" class="btn btn-success">New template</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead><tr><th>Name</th><th>Subject</th><th>Updated</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($templates as $template)
                            <tr>
                                <td>{{ $template->name }}</td>
                                <td>{{ $template->subject }}</td>
                                <td>{{ $template->updated_at->format('M j, Y') }}</td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('admin.crm.campaigns.create', ['template' => $template->id]) }}" class="btn btn-sm btn-primary">Use</a>
                                    <a href="{{ route('admin.crm.templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('admin.crm.templates.destroy', $template) }}" onsubmit="return confirm('Delete this template? Campaigns already created from it are not changed.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center">No templates yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $templates->links() }}</div>
    </div>
@endsection
