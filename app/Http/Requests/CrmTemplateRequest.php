<?php

namespace App\Http\Requests;

use App\Models\CrmTemplate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Validates a reusable CRM email template. */
class CrmTemplateRequest extends CrmEmailContentRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var CrmTemplate|null $template */
        $template = $this->route('template');

        return array_merge([
            'name' => ['required', 'string', 'max:255', Rule::unique('crm_templates', 'name')->ignore($template?->id)],
        ], $this->contentRules());
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [fn (Validator $validator) => $this->addContentErrors($validator, null, 0)];
    }
}
