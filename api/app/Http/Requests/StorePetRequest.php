<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePetRequest extends FormRequest
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
        $householdId = (int) $this->route('household');

        $avatar = $this->hasFile('avatar')
            ? ['avatar' => ['nullable', 'image', 'max:4096']]
            : ['avatar' => ['nullable', 'string', 'max:2048']];

        return [
            'name' => 'required|string|max:255',
            'species' => 'nullable|string|max:255',
            'breed' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'weight' => 'nullable|numeric|min:0',
            'quick_action_activity_type_ids' => ['sometimes', 'array'],
            'quick_action_activity_type_ids.*' => [
                'integer',
                Rule::exists('activity_types', 'id')->where('household_id', $householdId),
            ],
        ] + $avatar;
    }
}
