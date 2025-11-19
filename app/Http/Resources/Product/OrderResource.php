<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'order_number'      => $this->order_number,
            'user_id'           => $this->user_id,
            'status'            => $this->status,
            'total_amount'      => $this->total_amount,
            'shipping_address'  => $this->shipping_address,
            'billing_address'   => $this->billing_address,
            'created_at'        => formatDateTime($this->created_at),
            'updated_at'        => formatDateTime($this->updated_at),
            'items'             => OrderItemResource::collection($this->whenLoaded('orderItems')),
        ];
    }
}
