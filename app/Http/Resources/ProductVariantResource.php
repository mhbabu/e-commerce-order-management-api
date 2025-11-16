<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => $this->attributes,
            'price_modifier' => $this->price_modifier,
            'sku' => $this->sku,
            'is_active' => $this->is_active,
            'inventory' => new InventoryResource($this->whenLoaded('inventory')),
        ];
    }
}