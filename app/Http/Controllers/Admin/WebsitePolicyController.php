<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsitePolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Handles website policy editing in the admin panel.
 */
class WebsitePolicyController extends Controller
{
    /**
     * Show the editor for one supported website policy.
     */
    public function edit(string $policy): View
    {
        $title = $this->policyTitle($policy);
        $websitePolicy = WebsitePolicy::query()->where('slug', $policy)->first();

        return view('admin.website.policies.edit', [
            'policy' => $policy,
            'title' => $title,
            'content' => $websitePolicy?->content ?? '',
        ]);
    }

    /**
     * Save the text for one supported website policy.
     */
    public function update(Request $request, string $policy): RedirectResponse
    {
        $title = $this->policyTitle($policy);
        $validated = $this->validateData($request);

        try {
            DB::transaction(function () use ($policy, $validated): void {
                WebsitePolicy::query()->updateOrCreate(
                    ['slug' => $policy],
                    ['content' => $validated['content']],
                );
            });

            return redirect()
                ->route('admin.website.policies.edit', ['policy' => $policy])
                ->with('success', "{$title} saved successfully.");
        } catch (Throwable $exception) {
            Log::error('Website policy update failed.', [
                'event' => 'admin.website_policies.update',
                'policy' => $policy,
                'exception' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', "{$title} could not be saved. Please try again.");
        }
    }

    /**
     * Validate website policy content.
     *
     * @return array{content: string}
     */
    protected function validateData(Request $request): array
    {
        return $request->validate([
            'content' => ['required', 'string'],
        ]);
    }

    /**
     * Resolve a supported policy title or stop unknown policy requests.
     */
    private function policyTitle(string $policy): string
    {
        $title = WebsitePolicy::TITLES[$policy] ?? null;

        abort_if($title === null, 404);

        return $title;
    }
}
