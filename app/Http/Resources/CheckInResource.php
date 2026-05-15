<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckInResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'equipment_loan_id'   => $this->equipment_loan_id,
            'loan'                => new EquipmentLoanResource($this->whenLoaded('loan')),
            'user'                => new UserResource($this->whenLoaded('user')),
            'device_id'           => $this->device_id,
            'action'              => $this->action,
            'checked_at'          => $this->checked_at?->toIso8601String(),
            'latitude'            => $this->latitude,
            'longitude'           => $this->longitude,
            'notes'               => $this->notes,
            'created_at'          => $this->created_at?->toIso8601String(),
        ];
    }
}
