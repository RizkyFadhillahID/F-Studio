<?php

namespace App\Http\Requests\EquipmentLoan;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id'       => ['nullable', 'integer', 'exists:bookings,id'],
            'purpose'          => ['required', 'string'],
            'due_date'         => ['required', 'date', 'after_or_equal:today'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.equipment_id' => ['required', 'integer', 'exists:equipment,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.notes'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Minimal satu peralatan harus dipilih.',
            'due_date.after_or_equal' => 'Tanggal pengembalian tidak boleh di masa lalu.',
        ];
    }
}
