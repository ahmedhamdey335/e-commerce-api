<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\OrderItemResource;
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
            'id' => $this->id,
            'status' => $this->status,
            'total_price' => number_format($this->total_price / 100, 2),
            'address' => $this->address,
            'created_at' => $this->created_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
