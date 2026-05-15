<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentLoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'loan_code'   => $this->loan_code,
            'user'        => new UserResource($this->whenLoaded('user')),
            'booking'     => new BookingResource($this->whenLoaded('booking')),
            'purpose'     => $this->purpose,
            'status'      => $this->status,
            'notes'       => $this->notes,
            'admin_notes' => $this->admin_notes,
            'approved_by' => new UserResource($this->whenLoaded('approvedBy')),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'due_date'    => $this->due_date?->toDateString(),
            'returned_at' => $this->returned_at?->toIso8601String(),
            'items'       => EquipmentLoanItemResource::collection($this->whenLoaded('items')),
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
