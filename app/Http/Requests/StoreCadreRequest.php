<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCadreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/\D+/', '', (string) $this->input('phone'));
        if ($phone !== '' && str_starts_with($phone, '0')) $this->merge(['phone' => '62' . substr($phone, 1)]);
        $value = (string) $this->input('birth_date');
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            [$day, $month, $year] = explode('/', $value);
            $this->merge(['birth_date' => "$year-$month-$day"]);
        }
    }
    public function authorize(): bool { return in_array($this->user()?->role, ['admin', 'staff'], true); }
    public function rules(): array { return ['nik' => ['required', 'digits:16', 'unique:cadres,nik'], 'full_name' => ['required', 'string', 'max:255'], 'birth_place' => ['required', 'string', 'max:100'], 'birth_date' => ['required', 'date', 'before:today'], 'gender' => ['required', Rule::in(['L', 'P'])], 'phone' => ['required', 'string', 'max:20', 'unique:users,phone'], 'password' => ['required', 'string', 'min:8'], 'rt' => ['required', 'string', 'max:3'], 'rw' => ['required', 'string', 'max:3'], 'full_address' => ['required', 'string', 'max:1000'], 'working_area' => ['nullable', 'string', 'max:255'], 'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]; }
}
