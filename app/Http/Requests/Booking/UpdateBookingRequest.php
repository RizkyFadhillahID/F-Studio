<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'        => ['sometimes', 'integer', 'exists:rooms,id'],
            'title'          => ['sometimes', 'string', 'max:200'],
            'start_datetime' => ['sometimes', 'date', 'after:now'],
            'end_datetime'   => ['sometimes', 'date', 'after:start_datetime'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }
}
