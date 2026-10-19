<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\EventPriceTier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
        $isAdmin = $this->user()?->hasRole('admin') ?? false;

        return [
            'title' => ['required', 'string', 'max:255'],
            'sport' => ['required', 'string', 'max:100'],
            'content' => ['nullable', 'string', 'max:5000'],
            'summary' => ['nullable', 'string', 'max:500'],
            'date_of_event' => ['required', 'date'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'photos_live_at' => ['nullable', 'date'],
            'timezone' => ['required', 'timezone'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'size:2'],
            'country_code' => ['required', Rule::in(['US'])],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.50', 'max:999.99'],
            // Optional package prices keyed by order size; an empty one means no package at that size.
            'package_prices' => ['nullable', 'array:'.implode(',', EventPriceTier::PACKAGE_SIZES)],
            'package_prices.*' => ['nullable', 'numeric', 'decimal:0,2', 'min:0.50', 'max:999.99'],
            // Only administrators set pricing overrides; an empty value means the global default applies.
            'commission_percentage' => $isAdmin ? ['nullable', 'numeric', 'min:0', 'max:100'] : ['prohibited'],
            'image_url' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'status' => ['required', Rule::in([
                Event::STATUS_DRAFT,
                Event::STATUS_PUBLISHED,
                Event::STATUS_ARCHIVED,
            ])],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [fn (Validator $validator) => $this->rejectPackagePricesAboveSmallerOrders($validator)];
    }

    /** A larger order may never cost more per photo than a smaller one. */
    private function rejectPackagePricesAboveSmallerOrders(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['price', 'package_prices', 'package_prices.*'])) {
            return;
        }

        $previousLabel = '1-photo';
        $previousPriceCents = (int) round(((float) $this->input('price')) * 100);

        foreach (EventPriceTier::PACKAGE_SIZES as $minPhotos) {
            $packagePrice = $this->input("package_prices.{$minPhotos}");

            if (blank($packagePrice)) {
                continue;
            }

            $packagePriceCents = (int) round(((float) $packagePrice) * 100);

            if ($packagePriceCents > $previousPriceCents) {
                $validator->errors()->add(
                    "package_prices.{$minPhotos}",
                    "The {$minPhotos}+ photo price cannot be higher than the {$previousLabel} price.",
                );
            }

            $previousLabel = "{$minPhotos}+ photo";
            $previousPriceCents = $packagePriceCents;
        }
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
