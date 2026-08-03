<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'language' => ['required', 'string', Rule::in(['ar', 'fr', 'en'])],
            'goals' => ['sometimes', 'array', 'max:3'],
            'goals.*' => ['integer', 'distinct', Rule::exists('goals', 'id')],
        ];
    }
}