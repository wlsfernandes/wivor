<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\WebsitePolicy;
use Illuminate\View\View;

/**
 * Displays administrator-managed policy content on the public website.
 */
class PolicyController extends Controller
{
    /** @var array<string, array{meta_description: string, default_view: string}> */
    private const PRESENTATION = [
        WebsitePolicy::PRIVACY => [
            'meta_description' => 'How WivorPhotos handles customer, photographer, payment, event-photo, and recognition data.',
            'default_view' => 'policies.privacy',
        ],
        WebsitePolicy::TERMS => [
            'meta_description' => 'Terms for browsing WivorPhotos event galleries and purchasing digital event photographs.',
            'default_view' => 'policies.terms',
        ],
        WebsitePolicy::REFUND => [
            'meta_description' => 'How to request support and how WivorPhotos handles refunds for digital photograph purchases.',
            'default_view' => 'policies.refund',
        ],
        WebsitePolicy::PHOTOGRAPHER_TERMS => [
            'meta_description' => 'Terms for photographers who apply, publish event photographs, and earn through WivorPhotos.',
            'default_view' => 'policies.photographer-terms',
        ],
    ];

    /**
     * Display one supported public policy page.
     */
    public function show(string $policy): View
    {
        $title = WebsitePolicy::TITLES[$policy] ?? null;
        $presentation = self::PRESENTATION[$policy] ?? null;

        abort_if($title === null || $presentation === null, 404);

        $websitePolicy = WebsitePolicy::query()->where('slug', $policy)->first();
        $content = $websitePolicy?->content;

        if ($content === null || trim($content) === '') {
            return view($presentation['default_view']);
        }

        return view('policies.show', [
            'title' => $title,
            'metaDescription' => $presentation['meta_description'],
            'content' => $content,
            'lastUpdated' => $websitePolicy?->updated_at?->format('F j, Y'),
        ]);
    }
}
