<?php

namespace App\Http\Requests\CheckIn;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loan_code'                          => ['required', 'string', 'exists:equipment_loans,loan_code'],
            'action'                             => ['required', 'in:check_in,check_out'],
            'device_id'                          => ['nullable', 'string', 'max:100'],
            'notes'                              => ['nullable', 'string', 'max:500'],
            'latitude'                           => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'                          => ['nullable', 'numeric', 'between:-180,180'],
            'item_conditions'                    => ['required_if:action,check_out', 'array'],
            'item_conditions.*.item_id'          => ['required_if:action,check_out', 'integer', 'exists:equipment_loan_items,id'],
            'item_conditions.*.condition'        => ['required_if:action,check_out', 'in:excellent,good,fair,poor,maintenance'],
            'item_conditions.*.notes'            => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'loan_code.exists'            => 'Kode peminjaman tidak ditemukan.',
            'action.in'                   => 'Aksi harus berupa check_in atau check_out.',
            'item_conditions.required_if' => 'Kondisi item wajib diisi saat check-out.',
        ];
    }
}
