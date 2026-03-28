<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkActivityLogsRequest extends FormRequest
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
            'pet_ids' => ['required', 'array', 'min:1'],
            'pet_ids.*' => [
                'integer',
                Rule::exists('pets', 'id')->where('household_id', $householdId),
            ],
            'activity_type_id' => [
                'required',
                'integer',
                Rule::exists('activity_types', 'id')->where('household_id', $householdId),
            ],
            'value' => 'nullable|string|max:255',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'notes' => 'nullable|string',
        ];
    }
}

