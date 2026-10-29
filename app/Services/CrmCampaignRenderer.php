<?php

namespace App\Services;

use App\Models\CrmCampaign;

/**
 * Render campaign content for one recipient: personalize, sanitize again, wrap in the branded
 * layout with the compliance footer, and produce the plain-text alternative.
 *
 * Administrator HTML is passed to the layout as data and never compiled as a template.
 */
class CrmCampaignRenderer
{
    public const MISSING_POSTAL_ADDRESS = 'POSTAL ADDRESS NOT CONFIGURED';

    public function __construct(
        private readonly CrmPersonalizer $personalizer,
        private readonly CrmHtmlSanitizer $sanitizer,
    ) {
    }

    /** @param array<string, string|null> $values */
    public function render(CrmCampaign $campaign, array $values, ?string $unsubscribeUrl): CrmRenderedEmail
    {
        $values['unsubscribe_url'] = $unsubscribeUrl;
        $bodyHtml = $this->sanitizer->sanitizeFinal($this->personalizer->renderHtml($campaign->body_html, $values));
        $callToAction = $this->callToAction($campaign, $values);
        $footer = [
            'reason' => $this->reason($campaign, $values['event_name'] ?? null),
            'postalAddress' => $this->postalAddress(),
            'unsubscribeUrl' => $unsubscribeUrl,
        ];

        $subject = $this->singleLine($this->personalizer->renderText($campaign->subject, $values));

        $html = view('emails.crm.campaign', [
            'subject' => $subject,
            'previewText' => $this->personalizer->renderText((string) $campaign->preview_text, $values),
            'bodyHtml' => $bodyHtml,
            'callToAction' => $callToAction,
            'footer' => $footer,
        ])->render();

        return new CrmRenderedEmail(
            $subject,
            $html,
            $this->text($campaign, $values, $bodyHtml, $callToAction, $footer)
        );
    }

    /**
     * @param array<string, string|null> $values
     * @return array{label: string, url: string}|null
     */
    private function callToAction(CrmCampaign $campaign, array $values): ?array
    {
        if (blank($campaign->cta_label) || blank($campaign->cta_url)) {
            return null;
        }

        $url = $this->personalizer->renderText($campaign->cta_url, $values);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (filter_var($url, FILTER_VALIDATE_URL) === false || ! in_array($scheme, ['https', 'http'], true)) {
            return null;
        }

        return ['label' => $campaign->cta_label, 'url' => $url];
    }

    private function reason(CrmCampaign $campaign, ?string $eventName): string
    {
        if ($campaign->category !== CrmCampaign::CATEGORY_EVENT_NOTIFICATION) {
            return 'You are receiving this email because you agreed to receive news and offers from WiVor.';
        }

        return $eventName === null
            ? 'You are receiving this email because you took part in an event photographed by WiVor.'
            : "You are receiving this email because you took part in {$eventName}, photographed by WiVor.";
    }

    /** Subjects become a mail header, so imported names must not add line breaks to them. */
    private function singleLine(string $value): string
    {
        return trim(preg_replace('/\s*[\r\n]+\s*/', ' ', $value));
    }

    private function postalAddress(): string
    {
        $postalAddress = trim((string) config('crm.postal_address'));

        return $postalAddress === '' ? self::MISSING_POSTAL_ADDRESS : $postalAddress;
    }

    /**
     * @param array<string, string|null> $values
     * @param array{label: string, url: string}|null $callToAction
     * @param array{reason: string, postalAddress: string, unsubscribeUrl: string|null} $footer
     */
    private function text(CrmCampaign $campaign, array $values, string $bodyHtml, ?array $callToAction, array $footer): string
    {
        $sections = [
            filled($campaign->text_body)
                ? $this->personalizer->renderText($campaign->text_body, $values)
                : CrmPlainText::fromHtml($bodyHtml),
        ];

        if ($callToAction !== null) {
            $sections[] = $callToAction['label'].': '.$callToAction['url'];
        }

        $sections[] = "--\n".$footer['reason']."\n".$footer['postalAddress'];

        if ($footer['unsubscribeUrl'] !== null) {
            $sections[] = 'Unsubscribe: '.$footer['unsubscribeUrl'];
        }

        return implode("\n\n", $sections);
    }
}
