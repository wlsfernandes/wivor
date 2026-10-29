<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CrmImportStateException;
use App\Exceptions\InvalidCrmCsvException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCrmImportRequest;
use App\Models\CrmContactConsent;
use App\Models\CrmImport;
use App\Models\Event;
use App\Services\CrmImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Upload participant CSVs for an event and follow each import to completion.
 */
class CrmImportController extends Controller
{
    public function __construct(private readonly CrmImportService $importService)
    {
    }

    public function index(): View
    {
        return view('admin.crm.imports.index', [
            'imports' => CrmImport::with('event:id,title')->latest()->paginate(30),
        ]);
    }

    public function create(Event $event): View
    {
        return view('admin.crm.imports.create', [
            'event' => $event,
            'consentCategories' => [CrmContactConsent::CATEGORY_OPERATIONAL, CrmContactConsent::CATEGORY_MARKETING],
        ]);
    }

    public function store(StoreCrmImportRequest $request, Event $event): RedirectResponse
    {
        try {
            $import = $this->importService->createFromUpload($event, $request->user(), $request->file('file'), $request->provenance());
        } catch (InvalidCrmCsvException $exception) {
            return back()->withInput()->withErrors(['file' => $exception->getMessage()]);
        }

        return redirect()->route('admin.crm.imports.mapping.edit', $import);
    }

    public function show(CrmImport $import): View
    {
        return view('admin.crm.imports.show', [
            'import' => $import->load(['event:id,title', 'operator:id,name']),
            'hasErrorReport' => $this->errorReportExists($import),
        ]);
    }

    public function start(CrmImport $import): RedirectResponse
    {
        return $this->queue(fn () => $this->importService->start($import), $import);
    }

    public function retry(CrmImport $import): RedirectResponse
    {
        return $this->queue(fn () => $this->importService->retry($import), $import);
    }

    public function downloadErrors(CrmImport $import): StreamedResponse
    {
        abort_unless($this->errorReportExists($import), 404);

        return Storage::disk('local')->download($import->error_report_path, 'import-'.$import->id.'-errors.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function queue(callable $action, CrmImport $import): RedirectResponse
    {
        try {
            $action();
        } catch (CrmImportStateException $exception) {
            return back()->withErrors(['import' => $exception->getMessage()]);
        }

        return redirect()->route('admin.crm.imports.show', $import)->with('success', 'Import queued.');
    }

    private function errorReportExists(CrmImport $import): bool
    {
        return $import->error_report_path !== null
            && $import->files_purged_at === null
            && Storage::disk('local')->exists($import->error_report_path);
    }
}
