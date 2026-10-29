<?php

namespace App\Services;

/**
 * Derive the mandatory plain-text alternative from sanitized campaign HTML.
 */
class CrmPlainText
{
    public static function fromHtml(string $html): string
    {
        $text = preg_replace_callback(
            '/<a\b[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/is',
            function (array $match): string {
                $url = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);
                $label = trim(strip_tags($match[2]));

                return $label === '' || $label === $url ? $url : "{$label} ({$url})";
            },
            $html
        );

        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<li\b[^>]*>/i', "\n- ", $text);
        $text = preg_replace('/<\/(p|h[1-6]|ul|ol|blockquote)>/i', "\n\n", $text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5);
        $text = implode("\n", array_map('trim', explode("\n", $text)));

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }
}
