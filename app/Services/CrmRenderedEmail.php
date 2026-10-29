<?php

namespace App\Services;

/** A campaign email fully rendered for one recipient. */
final class CrmRenderedEmail
{
    public function __construct(
        public readonly string $subject,
        public readonly string $html,
        public readonly string $text,
    ) {
    }
}
