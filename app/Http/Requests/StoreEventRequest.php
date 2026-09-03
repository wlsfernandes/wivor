<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validates creation of a Wivor sports event. */
class StoreEventRequest extends FormRequest
{
    /** Allow administrators and photographers to create events. */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'sport' => ['required', 'string', 'max:100'],
            'content' => ['nullable', 'string', 'max:5000'],
            'summary' => ['nullable', 'string', 'max:500'],
            'date_of_event' => ['required', 'date'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'photos_live_at' => ['nullable', 'date'],
            'timezone' => ['required', 'timezone'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'size:2'],
            'country_code' => ['required', Rule::in(['US'])],
            'price' => ['required', 'numeric', 'min:0.50', 'max:999.99'],
            'image_url' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'status' => ['required', Rule::in([
                Event::STATUS_DRAFT,
                Event::STATUS_PUBLISHED,
                Event::STATUS_ARCHIVED,
            ])],
        ];
    }

    /** Normalize US location values before validation. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'state' => strtoupper((string) $this->input('state')),
            'country_code' => 'US',
            'timezone' => $this->input('timezone', 'America/New_York'),
            'status' => $this->input('status', Event::STATUS_DRAFT),
        ]);
    }
}
