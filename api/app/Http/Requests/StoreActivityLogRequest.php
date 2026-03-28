<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreActivityLogRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
            'value' => 'nullable|string|max:255',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'notes' => 'nullable|string',
            'feeding_plan_slot_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('feeding_plan_slots', 'id'),
            ],
        ];
    }
}
