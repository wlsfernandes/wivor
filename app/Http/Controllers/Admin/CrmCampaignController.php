<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CrmCampaignStateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CrmCampaignRequest;
use App\Models\CrmCampaign;
use App\Models\CrmTemplate;
use App\Models\Event;
use App\Services\CrmAudienceBuilder;
use App\Services\CrmCampaignContentValidator;
use App\Services\CrmCampaignService;
use App\Services\CrmPersonalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Create, edit and review CRM email campaigns.
 */
class CrmCampaignController extends Controller
{
    public function __construct(private readonly CrmCampaignService $campaignService)
    {
    }

    public function index(): View
    {
        return view('admin.crm.campaigns.index', [
            'campaigns' => CrmCampaign::withCount('events')->latest()->paginate(30),
        ]);
    }

    public function create(Request $request): View
    {
        $template = CrmTemplate::find($request->integer('template'));
        $campaign = new CrmCampaign(array_merge(
            ['category' => CrmCampaign::CATEGORY_EVENT_NOTIFICATION, 'audience_type' => CrmCampaign::AUDIENCE_EVENTS],
            $template?->only(['subject', 'preview_text', 'body_html', 'text_body', 'cta_label', 'cta_url']) ?? [],
            ['crm_template_id' => $template?->id]
        ));

        return view('admin.crm.campaigns.create', $this->formData($campaign, array_filter([$request->integer('event_id')])));
    }

    public function store(CrmCampaignRequest $request): RedirectResponse
    {
        $campaign = $this->campaignService->createDraft($request->validated(), $request->user());

        return redirect()->route('admin.crm.campaigns.show', $campaign)->with('success', 'Draft saved.');
    }

    public function show(CrmCampaign $campaign, CrmAudienceBuilder $audienceBuilder, CrmCampaignContentValidator $contentValidator): View
    {
        return view('admin.crm.campaigns.show', [
            'campaign' => $campaign->load(['events:id,title', 'author:id,name']),
            'counts' => $campaign->isDraft()
                ? $audienceBuilder->counts($campaign)
                : ['candidates' => $campaign->intended_count + $campaign->ineligible_count, 'eligible' => $campaign->intended_count, 'ineligible' => $campaign->ineligible_count],
            'contentErrors' => $contentValidator->campaignErrors($campaign),
            'hasPostalAddress' => filled(config('crm.postal_address')),
        ]);
    }

    public function edit(CrmCampaign $campaign): View|RedirectResponse
    {
        if (! $campaign->isDraft()) {
            return redirect()->route('admin.crm.campaigns.show', $campaign)->withErrors(['campaign' => 'Only draft campaigns can be edited.']);
        }

        return view('admin.crm.campaigns.edit', $this->formData($campaign, $campaign->events()->pluck('events.id')->all()));
    }

    public function update(CrmCampaignRequest $request, CrmCampaign $campaign): RedirectResponse
    {
        try {
            $this->campaignService->updateDraft($campaign, $request->validated());
        } catch (CrmCampaignStateException $exception) {
            return redirect()->route('admin.crm.campaigns.show', $campaign)->withErrors(['campaign' => $exception->getMessage()]);
        }

        return redirect()->route('admin.crm.campaigns.show', $campaign)->with('success', 'Draft updated.');
    }

    public function destroy(CrmCampaign $campaign): RedirectResponse
    {
        try {
            $this->campaignService->deleteDraft($campaign);
        } catch (CrmCampaignStateException $exception) {
            return back()->withErrors(['campaign' => $exception->getMessage()]);
        }

        return redirect()->route('admin.crm.campaigns.index')->with('success', 'Draft deleted.');
    }

    /** @param list<int> $selectedEventIds */
    private function formData(CrmCampaign $campaign, array $selectedEventIds): array
    {
        return [
            'campaign' => $campaign,
            'selectedEventIds' => $selectedEventIds,
            'events' => Event::orderByDesc('date_of_event')->orderBy('title')->get(['id', 'title', 'date_of_event']),
            'templates' => CrmTemplate::orderBy('name')->get(['id', 'name']),
            'categories' => CrmCampaign::CATEGORIES,
            'placeholders' => CrmPersonalizer::PLACEHOLDERS,
        ];
    }
}
