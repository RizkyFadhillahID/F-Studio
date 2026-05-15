<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentLoanItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'equipment'           => new EquipmentResource($this->whenLoaded('equipment')),
            'quantity'            => $this->quantity,
            'condition_on_loan'   => $this->condition_on_loan,
            'condition_on_return' => $this->condition_on_return,
            'check_in_at'         => $this->check_in_at?->toIso8601String(),
            'check_out_at'        => $this->check_out_at?->toIso8601String(),
            'notes'               => $this->notes,
        ];
    }
}
