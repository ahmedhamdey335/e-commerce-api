<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => number_format($this->price / 100, 2), // Convert cents to dollars
            'stock' => $this->stock,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'image_url' => $this->image ? Storage::url($this->image) : null,
        ];
    }
}
