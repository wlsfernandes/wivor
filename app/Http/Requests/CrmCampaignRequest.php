<?php

namespace App\Http\Requests;

use App\Models\CrmCampaign;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Validates a draft campaign's purpose, audience and content. */
class CrmCampaignRequest extends CrmEmailContentRequest
{
    public const MAX_SELECTED_EVENTS = 20;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(CrmCampaign::CATEGORIES))],
            'audience_type' => [
                'required',
                Rule::in([CrmCampaign::AUDIENCE_EVENTS, CrmCampaign::AUDIENCE_DIRECTORY]),
                Rule::when(
                    $this->input('category') === CrmCampaign::CATEGORY_EVENT_NOTIFICATION,
                    [Rule::in([CrmCampaign::AUDIENCE_EVENTS])]
                ),
            ],
            'event_ids' => ['exclude_unless:audience_type,'.CrmCampaign::AUDIENCE_EVENTS, 'required', 'array', 'max:'.self::MAX_SELECTED_EVENTS],
            'event_ids.*' => ['integer', 'distinct', 'exists:events,id'],
            'crm_template_id' => ['nullable', 'integer', 'exists:crm_templates,id'],
        ], $this->contentRules());
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'audience_type.in' => 'Event notifications can only be sent to participants of selected events.',
            'event_ids.required' => 'Choose at least one event.',
        ]);
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [fn (Validator $validator) => $this->addContentErrors(
            $validator,
            $this->input('audience_type'),
            count((array) $this->input('event_ids', []))
        )];
    }
}
