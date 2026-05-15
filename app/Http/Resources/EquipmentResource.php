<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'category_id'        => $this->category_id,
            'category'           => new CategoryResource($this->whenLoaded('category')),
            'name'               => $this->name,
            'code'               => $this->code,
            'description'        => $this->description,
            'quantity_total'     => $this->quantity_total,
            'quantity_available' => $this->quantity_available,
            'condition'          => $this->condition,
            'location'           => $this->location,
            'image'              => $this->image ? asset('storage/' . $this->image) : null,
            'is_active'          => $this->is_active,
            'created_at'         => $this->created_at?->toIso8601String(),
            'updated_at'         => $this->updated_at?->toIso8601String(),
        ];
    }
}
