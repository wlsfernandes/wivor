<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CrmCampaignStateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleCrmCampaignRequest;
use App\Models\CrmCampaign;
use App\Services\CrmCampaignRenderer;
use App\Services\CrmCampaignService;
use App\Services\CrmRecipientValues;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Preview, test, schedule and cancel campaigns.
 */
class CrmCampaignActionController extends Controller
{
    public function __construct(private readonly CrmCampaignService $campaignService)
    {
    }

    public function preview(Request $request, CrmCampaign $campaign, CrmCampaignRenderer $renderer): View
    {
        return view('admin.crm.campaigns.preview', [
            'campaign' => $campaign,
            'email' => $renderer->render(
                $campaign,
                CrmRecipientValues::sample($request->user(), $this->campaignService->eventContext($campaign)),
                null
            ),
        ]);
    }

    public function testSend(Request $request, CrmCampaign $campaign): RedirectResponse
    {
        $this->campaignService->sendTest($campaign, $request->user());

        return redirect()->route('admin.crm.campaigns.show', $campaign)
            ->with('success', "Test email queued to {$request->user()->email}. Only you receive it.");
    }

    public function schedule(ScheduleCrmCampaignRequest $request, CrmCampaign $campaign): RedirectResponse
    {
        $sendAt = Carbon::parse($request->validated('scheduled_at'), CrmCampaign::SCHEDULE_TIMEZONE);

        return $this->perform(fn () => $this->campaignService->schedule($campaign, $sendAt), $campaign, 'Campaign scheduled.');
    }

    public function unschedule(CrmCampaign $campaign): RedirectResponse
    {
        return $this->perform(fn () => $this->campaignService->unschedule($campaign), $campaign, 'Campaign moved back to draft.');
    }

    public function cancel(CrmCampaign $campaign): RedirectResponse
    {
        return $this->perform(fn () => $this->campaignService->cancel($campaign), $campaign, 'Campaign cancelled.');
    }

    private function perform(callable $action, CrmCampaign $campaign, string $successMessage): RedirectResponse
    {
        try {
            $action();
        } catch (CrmCampaignStateException $exception) {
            return redirect()->route('admin.crm.campaigns.show', $campaign)->withErrors(['campaign' => $exception->getMessage()]);
        }

        return redirect()->route('admin.crm.campaigns.show', $campaign)->with('success', $successMessage);
    }
}
