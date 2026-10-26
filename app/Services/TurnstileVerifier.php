<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Verify Cloudflare Turnstile tokens server side; any failure is treated as not verified. */
class TurnstileVerifier
{
    private const SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private const TIMEOUT_SECONDS = 5;

    public function verify(string $token, ?string $remoteIp): bool
    {
        $secretKey = config('services.turnstile.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            Log::error('Turnstile secret key is not configured.', ['event' => 'turnstile.verify']);

            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(self::TIMEOUT_SECONDS)
                ->post(self::SITEVERIFY_URL, array_filter([
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]));
        } catch (ConnectionException $exception) {
            Log::warning('Turnstile verification service is unreachable.', [
                'event' => 'turnstile.verify',
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful() || ! is_array($response->json())) {
            Log::warning('Turnstile verification returned an unexpected response.', [
                'event' => 'turnstile.verify',
                'status' => $response->status(),
            ]);

            return false;
        }

        if ($response->json('success') !== true) {
            Log::info('Turnstile verification rejected a token.', [
                'event' => 'turnstile.verify',
                'error_codes' => $response->json('error-codes', []),
            ]);

            return false;
        }

        return true;
    }
}
