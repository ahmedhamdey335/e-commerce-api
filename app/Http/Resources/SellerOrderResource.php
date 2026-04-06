<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'status' => $this->status,
            'ordered_at' => $this->created_at,
            'seller_items' => $this->whenLoaded('items', function () use ($request) {
                return $this->items
                    ->filter(fn($item) => $item->product?->user_id === $request->user()->id)
                    ->map(fn($item) => [
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => number_format($item->price / 100, 2),
                    ])
                    ->values(); // resets array keys after filter
            }),
        ];
    }
}