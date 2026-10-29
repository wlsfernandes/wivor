<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CrmTemplateRequest;
use App\Models\CrmTemplate;
use App\Services\CrmPersonalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Manage reusable CRM email templates.
 */
class CrmTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.crm.templates.index', [
            'templates' => CrmTemplate::orderBy('name')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.crm.templates.create', [
            'template' => new CrmTemplate,
            'placeholders' => CrmPersonalizer::PLACEHOLDERS,
        ]);
    }

    public function store(CrmTemplateRequest $request): RedirectResponse
    {
        CrmTemplate::create($request->validated());

        return redirect()->route('admin.crm.templates.index')->with('success', 'Template saved.');
    }

    public function edit(CrmTemplate $template): View
    {
        return view('admin.crm.templates.edit', [
            'template' => $template,
            'placeholders' => CrmPersonalizer::PLACEHOLDERS,
        ]);
    }

    public function update(CrmTemplateRequest $request, CrmTemplate $template): RedirectResponse
    {
        $template->update(array_merge(
            ['preview_text' => null, 'text_body' => null, 'cta_label' => null, 'cta_url' => null],
            $request->validated()
        ));

        return redirect()->route('admin.crm.templates.index')->with('success', 'Template updated.');
    }

    public function destroy(CrmTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('admin.crm.templates.index')->with('success', 'Template deleted.');
    }
}
