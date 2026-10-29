<?php

namespace App\Http\Requests;

use App\Models\CrmImport;
use App\Services\CrmCsvReader;
use App\Services\CrmImportRowMapper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Validates that a column mapping only refers to headers present in the uploaded CSV. */
class UpdateCrmImportMappingRequest extends FormRequest
{
    public const MAX_EXTRA_FIELDS = 10;

    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var CrmImport $import */
        $import = $this->route('import');
        $headerRule = Rule::in(CrmCsvReader::fromPath(Storage::disk('local')->path($import->disk_path))->headers());

        $rules = [
            'mapping' => ['required', 'array'],
            'mapping.email' => ['required', 'string', $headerRule],
            'mapping.extra_fields' => ['nullable', 'array', 'max:'.self::MAX_EXTRA_FIELDS],
            'mapping.extra_fields.*' => ['string', 'distinct', $headerRule],
            'preset_name' => ['nullable', 'string', 'max:100', 'unique:crm_mapping_presets,name'],
        ];

        foreach (array_keys(CrmImportRowMapper::FIELDS) as $field) {
            $rules['mapping.'.$field] = ['nullable', 'string', $headerRule];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'mapping.email.required' => 'Choose the column that holds the email address.',
            'mapping.email.in' => 'The email column must be one of the file headers.',
        ];
    }

    /** The mapping without unmapped fields. */
    public function mapping(): array
    {
        return array_filter($this->validated('mapping'), fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
