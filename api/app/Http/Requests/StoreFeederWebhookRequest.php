<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeederWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('detections') && is_string($this->input('detections'))) {
            $decoded = json_decode($this->input('detections'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['detections' => $decoded]);
            }
        }

        if ($this->has('confidence') && is_string($this->input('confidence'))) {
            $this->merge(['confidence' => (float) $this->input('confidence')]);
        }
    }

    public function rules(): array
    {
        return [
            'timestamp' => ['required', 'date'],
            'label' => ['required', 'string', 'max:255'],
            'action' => ['required', Rule::in(['open', 'stay_closed', 'none'])],
            'confidence' => ['required', 'numeric', 'min:0', 'max:1'],
            'mouth_status' => ['nullable', 'string', 'max:255'],
            'detections' => ['present', 'array'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg', 'max:5120'],
        ];
    }
}
