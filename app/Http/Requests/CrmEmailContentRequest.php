<?php

namespace App\Http\Requests;

use App\Services\CrmCampaignContentValidator;
use App\Services\CrmHtmlSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Shared validation and sanitizing for email content written in the CRM editor. */
abstract class CrmEmailContentRequest extends FormRequest
{
    public const MAX_TEXT_BODY_LENGTH = 50000;

    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /** Sanitize the editor HTML before validation so rules and storage see the same content. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('body_html'))) {
            $this->merge(['body_html' => app(CrmHtmlSanitizer::class)->sanitizeDraft($this->input('body_html'))]);
        }
    }

    /** @return array<string, mixed> */
    protected function contentRules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'preview_text' => ['nullable', 'string', 'max:255'],
            'body_html' => ['required', 'string', 'max:'.CrmHtmlSanitizer::MAX_INPUT_LENGTH],
            'text_body' => ['nullable', 'string', 'max:'.self::MAX_TEXT_BODY_LENGTH],
            'cta_label' => ['nullable', 'required_with:cta_url', 'string', 'max:60'],
            'cta_url' => ['nullable', 'required_with:cta_label', 'string', 'max:2048', 'regex:#^(https?://\S+|\{\{\s*(event_url|photo_gallery_url)\s*\}\})$#'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cta_url.regex' => 'The button link must be a full https:// address, {{event_url}} or {{photo_gallery_url}}.',
        ];
    }

    /** Placeholder and link checks; the audience is null for templates. */
    protected function addContentErrors(Validator $validator, ?string $audienceType, int $selectedEventCount): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $errors = app(CrmCampaignContentValidator::class)->errors(
            $this->only(['subject', 'preview_text', 'body_html', 'text_body', 'cta_url']),
            $audienceType,
            $selectedEventCount
        );

        foreach ($errors as $error) {
            $validator->errors()->add('content', $error);
        }
    }
}
