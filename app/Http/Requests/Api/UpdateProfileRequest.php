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
            'language' => ['required', 'string', Rule::in(['ar', 'fr', 'en'])],
            'goals' => ['required', 'array', 'min:2', 'max:3'],
            'goals.*' => ['integer', 'distinct', Rule::exists('goals', 'id')],
        ];
    }
}