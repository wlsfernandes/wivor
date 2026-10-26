<?php

namespace App\Rules;

use App\Services\TurnstileVerifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Passes only when Cloudflare confirms the submitted Turnstile token. */
class TurnstileToken implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! app(TurnstileVerifier::class)->verify($value, request()->ip())) {
            $fail('Security check failed or expired. Please complete it again and resubmit.');
        }
    }
}
