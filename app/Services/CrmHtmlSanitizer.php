<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Restrict administrator-authored email HTML to simple formatting, https images and safe links.
 *
 * Drafts keep placeholder links such as href="{{event_url}}"; the final pass, run after
 * personalization, allows only absolute links.
 */
class CrmHtmlSanitizer
{
    public const MAX_INPUT_LENGTH = 200000;

    private const TEXT_ELEMENTS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote'];

    public function sanitizeDraft(string $html): string
    {
        return $this->sanitizer(allowRelativeLinks: true)->sanitize($html);
    }

    public function sanitizeFinal(string $html): string
    {
        return $this->sanitizer(allowRelativeLinks: false)->sanitize($html);
    }

    private function sanitizer(bool $allowRelativeLinks): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig)
            ->allowElement('a', ['href', 'title'])
            ->allowElement('img', ['src', 'alt', 'width', 'height'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->allowRelativeLinks($allowRelativeLinks)
            ->allowMediaSchemes(['https'])
            ->allowRelativeMedias(false)
            ->withMaxInputLength(self::MAX_INPUT_LENGTH);

        foreach (self::TEXT_ELEMENTS as $element) {
            $config = $config->allowElement($element);
        }

        return new HtmlSanitizer($config);
    }
}
