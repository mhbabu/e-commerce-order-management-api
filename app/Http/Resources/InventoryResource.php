<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'quantity'            => $this->quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'last_updated'        => formatDateTime($this->updated_at),
        ];
    }
}