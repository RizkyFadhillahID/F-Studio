<?php

namespace App\Http\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('equipment')?->id ?? $this->route('equipment');

        return [
            'category_id'        => ['sometimes', 'integer', 'exists:categories,id'],
            'name'               => ['sometimes', 'string', 'max:150'],
            'code'               => ['sometimes', 'string', 'max:50', "unique:equipment,code,{$id}"],
            'description'        => ['nullable', 'string'],
            'quantity_total'     => ['sometimes', 'integer', 'min:1'],
            'quantity_available' => ['nullable', 'integer', 'min:0'],
            'condition'          => ['sometimes', 'in:excellent,good,fair,poor,maintenance'],
            'location'           => ['nullable', 'string', 'max:100'],
            'image'              => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'          => ['nullable', 'boolean'],
        ];
    }
}
