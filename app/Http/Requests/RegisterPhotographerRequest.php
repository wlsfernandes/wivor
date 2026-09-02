<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validate a public photographer application. */
class RegisterPhotographerRequest extends FormRequest
{
    /** Public visitors may submit a photographer application. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'size:2'],
            'profile_url' => ['required', 'url', 'max:255'],
            'camera_model' => ['required', 'string', 'max:500'],
            'about' => ['required', 'string', 'max:2000'],
            'is_adult' => ['accepted'],
            'accepts_terms' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered. Please log in instead.',
            'is_adult.accepted' => 'You must confirm that you are at least 18 years old.',
            'accepts_terms.accepted' => 'You must accept the photographer terms.',
        ];
    }
}
