<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHabitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'frequency' => 'required|in:daily,weekly,custom',
            'custom_days' => 'nullable|array',
            'custom_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'reminder_time' => 'nullable|date_format:H:i',
        ];
    }
}
