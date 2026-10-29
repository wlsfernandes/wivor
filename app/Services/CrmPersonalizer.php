<?php

namespace App\Services;

/**
 * Replace the allowlisted {{placeholders}} in campaign content; nothing else is evaluated.
 */
class CrmPersonalizer
{
    public const PLACEHOLDERS = [
        'first_name',
        'last_name',
        'event_name',
        'event_date',
        'event_url',
        'photo_gallery_url',
        'bib_number',
        'unsubscribe_url',
    ];

    /** Placeholders that only make sense when a campaign targets exactly one event. */
    public const EVENT_PLACEHOLDERS = ['event_name', 'event_date', 'event_url', 'photo_gallery_url', 'bib_number'];

    private const FALLBACKS = ['first_name' => 'there'];

    private const PLACEHOLDER_PATTERN = '/\{\{\s*([A-Za-z_]+)\s*\}\}/';

    /** @return list<string> */
    public function unknownPlaceholders(string $content): array
    {
        return array_values(array_map(
            fn (string $name): string => '{{'.$name.'}}',
            array_diff($this->placeholderNames($content), self::PLACEHOLDERS)
        ));
    }

    /** @return list<string> */
    public function eventPlaceholders(string $content): array
    {
        return array_values(array_map(
            fn (string $name): string => '{{'.$name.'}}',
            array_intersect($this->placeholderNames($content), self::EVENT_PLACEHOLDERS)
        ));
    }

    /** @param array<string, string|null> $values */
    public function renderHtml(string $content, array $values): string
    {
        return $this->render($content, $values, fn (string $value): string => e($value));
    }

    /** @param array<string, string|null> $values */
    public function renderText(string $content, array $values): string
    {
        return $this->render($content, $values, fn (string $value): string => $value);
    }

    /**
     * @param array<string, string|null> $values
     * @param callable(string): string $escape
     */
    private function render(string $content, array $values, callable $escape): string
    {
        return preg_replace_callback(self::PLACEHOLDER_PATTERN, function (array $match) use ($values, $escape): string {
            $name = $match[1];

            if (! in_array($name, self::PLACEHOLDERS, true)) {
                return '';
            }

            $value = $values[$name] ?? null;

            return $escape($value === null || $value === '' ? (self::FALLBACKS[$name] ?? '') : $value);
        }, $content);
    }

    /** @return list<string> */
    private function placeholderNames(string $content): array
    {
        preg_match_all(self::PLACEHOLDER_PATTERN, $content, $matches);

        return array_values(array_unique($matches[1]));
    }
}
