<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'        => ['required', 'integer', 'exists:rooms,id'],
            'title'          => ['required', 'string', 'max:200'],
            'start_datetime' => ['required', 'date', 'after:now'],
            'end_datetime'   => ['required', 'date', 'after:start_datetime'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_datetime.after' => 'Waktu selesai harus setelah waktu mulai.',
            'start_datetime.after' => 'Waktu mulai harus di masa mendatang.',
        ];
    }
}
