<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $householdId = $this->route('household');

        return $this->user()->households()->where('households.id', $householdId)->exists();
    }

    public function rules(): array
    {
        $householdId = $this->route('household');

        return [
            'name' => ['required', 'string', 'max:255'],
            'pet_ids' => ['nullable', 'array'],
            'pet_ids.*' => [
                'integer',
                Rule::exists('pets', 'id')->where('household_id', $householdId),
            ],
            'slots' => ['nullable', 'array'],
            'slots.*.activity_type_id' => [
                'required',
                'integer',
                Rule::exists('activity_types', 'id')->where('household_id', $householdId),
            ],
            'slots.*.time' => ['required', 'date_format:H:i'],
            'slots.*.weekdays' => ['required', 'array', 'min:1'],
            'slots.*.weekdays.*' => ['integer', 'between:1,7'],
            'slots.*.title' => ['nullable', 'string', 'max:255'],
            'slots.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
