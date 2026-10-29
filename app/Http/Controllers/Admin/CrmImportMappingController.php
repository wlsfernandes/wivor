<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CrmImportStateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCrmImportMappingRequest;
use App\Models\CrmImport;
use App\Models\CrmMappingPreset;
use App\Services\CrmCsvReader;
use App\Services\CrmImportPreview;
use App\Services\CrmImportRowMapper;
use App\Services\CrmImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Map an uploaded CSV's columns to CRM fields and preview the result before importing.
 */
class CrmImportMappingController extends Controller
{
    public function __construct(private readonly CrmImportService $importService)
    {
    }

    public function edit(Request $request, CrmImport $import): View|RedirectResponse
    {
        if ($import->status !== CrmImport::STATUS_PENDING) {
            return $this->alreadyStarted($import);
        }

        $presets = CrmMappingPreset::orderBy('name')->get(['id', 'name', 'mapping']);
        $selectedPreset = $presets->firstWhere('id', (int) $request->query('preset'));

        return view('admin.crm.imports.mapping', [
            'import' => $import->load('event:id,title'),
            'headers' => CrmCsvReader::fromPath(Storage::disk('local')->path($import->disk_path))->headers(),
            'fields' => CrmImportRowMapper::FIELDS,
            'presets' => $presets,
            'mapping' => $selectedPreset?->mapping ?? $import->mapping,
        ]);
    }

    public function update(UpdateCrmImportMappingRequest $request, CrmImport $import): RedirectResponse
    {
        try {
            $this->importService->saveMapping($import, $request->mapping(), $request->validated('preset_name'));
        } catch (CrmImportStateException) {
            return $this->alreadyStarted($import);
        }

        return redirect()->route('admin.crm.imports.preview', $import);
    }

    public function preview(CrmImport $import, CrmImportPreview $preview): View|RedirectResponse
    {
        if ($import->status !== CrmImport::STATUS_PENDING) {
            return $this->alreadyStarted($import);
        }

        if (! isset($import->mapping['email'])) {
            return redirect()->route('admin.crm.imports.mapping.edit', $import);
        }

        return view('admin.crm.imports.preview', array_merge(
            ['import' => $import->load('event:id,title')],
            $preview->build($import)
        ));
    }

    private function alreadyStarted(CrmImport $import): RedirectResponse
    {
        return redirect()->route('admin.crm.imports.show', $import)->withErrors(['import' => 'This import has already started.']);
    }
}
