<?php

namespace App\Http\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'name'             => ['required', 'string', 'max:150'],
            'code'             => ['required', 'string', 'max:50', 'unique:equipment,code'],
            'description'      => ['nullable', 'string'],
            'quantity_total'   => ['required', 'integer', 'min:1'],
            'quantity_available' => ['nullable', 'integer', 'min:0'],
            'condition'        => ['required', 'in:excellent,good,fair,poor,maintenance'],
            'location'         => ['nullable', 'string', 'max:100'],
            'image'            => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'        => ['nullable', 'boolean'],
        ];
    }
}
