<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReminderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $householdId = $this->route('household');
        return $this->user()->households()->where('households.id', $householdId)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $householdId = $this->route('household');

        return [
            'pet_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('pets', 'id')->where('household_id', $householdId),
            ],
            'activity_type_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('activity_types', 'id')->where('household_id', $householdId),
            ],
            'title' => 'required|string|max:255',
            'time' => 'required|date_format:H:i',
            'frequency' => 'required|string|in:daily,weekly,monthly,custom',
            'is_active' => 'boolean',
        ];
    }
}
