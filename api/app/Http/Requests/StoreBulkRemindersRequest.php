<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkRemindersRequest extends FormRequest
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
            'pet_ids' => ['required', 'array', 'min:2'],
            'pet_ids.*' => [
                'integer',
                Rule::exists('pets', 'id')->where('household_id', $householdId),
            ],
            'activity_type_id' => [
                'required',
                'integer',
                Rule::exists('activity_types', 'id')->where('household_id', $householdId),
            ],
            'title' => 'required|string|max:255',
            'time' => 'required|date_format:H:i',
            'frequency' => 'required|string|in:daily,weekly,monthly,custom',
            'is_active' => 'boolean',
        ];
    }
}
