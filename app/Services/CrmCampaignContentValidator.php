<?php

namespace App\Services;

use App\Models\CrmCampaign;

/**
 * Check campaign content placeholders against the allowlist and the chosen audience.
 */
class CrmCampaignContentValidator
{
    public function __construct(private readonly CrmPersonalizer $personalizer)
    {
    }

    /**
     * @param array{subject?: string|null, preview_text?: string|null, body_html?: string|null, text_body?: string|null, cta_url?: string|null} $content
     * @return list<string>
     */
    public function errors(array $content, ?string $audienceType, int $selectedEventCount): array
    {
        $allContent = implode("\n", array_map('strval', array_intersect_key(
            $content,
            array_flip(['subject', 'preview_text', 'body_html', 'text_body', 'cta_url'])
        )));
        $errors = [];

        foreach ($this->personalizer->unknownPlaceholders($allContent) as $placeholder) {
            $errors[] = "{$placeholder} is not an available placeholder.";
        }

        foreach ($this->linkTargets((string) ($content['body_html'] ?? '')) as $target) {
            if (! $this->isAllowedLinkTarget($target)) {
                $errors[] = "The link \"{$target}\" must be a full https:// address or a placeholder such as {{event_url}}.";
            }
        }

        // Templates are checked without an audience; the event rule applies once a campaign chooses one.
        $hasSingleEvent = $audienceType === CrmCampaign::AUDIENCE_EVENTS && $selectedEventCount === 1;

        if ($audienceType !== null && ! $hasSingleEvent) {
            foreach ($this->personalizer->eventPlaceholders($allContent) as $placeholder) {
                $errors[] = "{$placeholder} can only be used when the campaign targets exactly one event.";
            }
        }

        return $errors;
    }

    /** @return list<string> */
    private function linkTargets(string $bodyHtml): array
    {
        preg_match_all('/<a\b[^>]*\bhref="([^"]*)"/i', $bodyHtml, $matches);

        return array_values(array_unique(array_map(
            fn (string $target): string => html_entity_decode($target, ENT_QUOTES | ENT_HTML5),
            $matches[1]
        )));
    }

    private function isAllowedLinkTarget(string $target): bool
    {
        if (preg_match('/^\{\{\s*(event_url|photo_gallery_url|unsubscribe_url)\s*\}\}$/', $target) === 1) {
            return true;
        }

        return preg_match('#^(https?://[^\s]+|mailto:[^\s]+)$#i', $target) === 1;
    }

    /** @return list<string> */
    public function campaignErrors(CrmCampaign $campaign): array
    {
        return $this->errors($campaign->only(['subject', 'preview_text', 'body_html', 'text_body', 'cta_url']), $campaign->audience_type, $campaign->events()->count());
    }
}
