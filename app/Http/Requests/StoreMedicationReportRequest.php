<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationReportRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['patient_medication_plan_id' => ['nullable', 'integer', 'exists:patient_medication_plans,id'], 'schedule_time_id' => ['required', 'integer', 'exists:medication_schedule_times,id'], 'medication_taken' => ['required', 'boolean'], 'not_taken_reason' => ['nullable', 'string', 'max:2000', Rule::requiredIf(! $this->boolean('medication_taken'))], 'has_side_effect' => ['required', 'boolean'], 'side_effect_category' => ['nullable', 'string', 'max:100'], 'side_effect_description' => ['nullable', 'string', 'max:2000', Rule::requiredIf($this->boolean('has_side_effect'))], 'photo' => ['required', 'file', 'mimetypes:image/jpeg,image/webp', 'max:5120'], 'latitude' => ['required', 'numeric', 'between:-90,90'], 'longitude' => ['required', 'numeric', 'between:-180,180'], 'gps_accuracy' => ['required', 'numeric', 'min:0']]; }
}
