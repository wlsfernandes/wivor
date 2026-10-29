<?php

namespace App\Http\Requests;

use App\Models\CrmContactConsent;
use App\Models\CrmImport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validates a participant CSV upload and the provenance an administrator declares for it. */
class StoreCrmImportRequest extends FormRequest
{
    public const MAX_FILE_KILOBYTES = 5120;

    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $grantedConsent = 'required_if:consent_status,'.CrmContactConsent::STATUS_GRANTED;

        return [
            'file' => ['required', 'file', 'max:'.self::MAX_FILE_KILOBYTES, 'mimes:csv,txt', 'extensions:csv,txt'],
            'data_source' => ['required', 'string', 'max:255'],
            'organizer_name' => ['nullable', 'string', 'max:255'],
            'consent_status' => ['required', Rule::in([CrmContactConsent::STATUS_UNKNOWN, CrmContactConsent::STATUS_GRANTED])],
            'consent_category' => [$grantedConsent, 'nullable', Rule::in([CrmContactConsent::CATEGORY_OPERATIONAL, CrmContactConsent::CATEGORY_MARKETING])],
            'consent_scope' => [$grantedConsent, 'nullable', Rule::in([CrmImport::CONSENT_SCOPE_EVENT, CrmImport::CONSENT_SCOPE_WIVOR])],
            'consent_evidence' => [$grantedConsent, 'nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'consent_evidence.required_if' => 'Describe the consent evidence (for example the registration form wording) before marking consent as granted.',
        ];
    }

    /** Provenance fields, with consent details cleared unless consent was granted. */
    public function provenance(): array
    {
        $provenance = $this->safe()->except('file');

        if ($provenance['consent_status'] !== CrmContactConsent::STATUS_GRANTED) {
            $provenance['consent_category'] = null;
            $provenance['consent_scope'] = null;
            $provenance['consent_evidence'] = null;
        }

        return $provenance;
    }
}
