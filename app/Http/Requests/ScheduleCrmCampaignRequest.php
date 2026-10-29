<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validates the send time an administrator picks for a campaign, in Eastern time. */
class ScheduleCrmCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date'],
        ];
    }
}
