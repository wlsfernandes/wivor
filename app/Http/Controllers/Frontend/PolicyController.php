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
    /** @var array<string, array{meta_description: string, placeholder: string}> */
    private const PRESENTATION = [
        WebsitePolicy::PRIVACY => [
            'meta_description' => 'Placeholder for the forthcoming WivorPhotos Privacy Policy.',
            'placeholder' => 'WivorPhotos is preparing its full Privacy Policy. This page will be updated with information regarding how personal information, photographs, transaction information, and related data are collected, used, stored, and protected.',
        ],
        WebsitePolicy::TERMS => [
            'meta_description' => 'Placeholder for the forthcoming WivorPhotos Terms of Use.',
            'placeholder' => 'WivorPhotos is preparing its full Terms of Use. This page will be updated with the terms governing use of the WivorPhotos website, event galleries, purchases, photographer participation, and related services.',
        ],
        WebsitePolicy::REFUND => [
            'meta_description' => 'Placeholder for the forthcoming WivorPhotos Refund Policy.',
            'placeholder' => 'WivorPhotos is preparing its full Refund Policy. This page will be updated with information regarding eligibility, requests, exceptions, and the handling of digital-photo purchases.',
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

        return view('policies.show', [
            'title' => $title,
            'metaDescription' => $presentation['meta_description'],
            'placeholder' => $presentation['placeholder'],
            'content' => $content,
            'hasContent' => $content !== null && trim($content) !== '',
            'lastUpdated' => $websitePolicy?->updated_at?->format('F j, Y'),
        ]);
    }
}
