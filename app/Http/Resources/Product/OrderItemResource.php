<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'product_name'       => $this->productVariant->product->name ?? null,
            'variant_name'       => $this->productVariant->sku ?? null,
            'quantity'           => $this->quantity,
            'price'              => $this->price,
            'total'              => $this->price * $this->quantity
        ];
    }
}
