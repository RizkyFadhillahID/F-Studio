<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'booking_code'   => $this->booking_code,
            'user'           => new UserResource($this->whenLoaded('user')),
            'room'           => new RoomResource($this->whenLoaded('room')),
            'title'          => $this->title,
            'start_datetime' => $this->start_datetime?->toIso8601String(),
            'end_datetime'   => $this->end_datetime?->toIso8601String(),
            'status'         => $this->status,
            'notes'          => $this->notes,
            'admin_notes'    => $this->admin_notes,
            'approved_by'    => new UserResource($this->whenLoaded('approvedBy')),
            'approved_at'    => $this->approved_at?->toIso8601String(),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
