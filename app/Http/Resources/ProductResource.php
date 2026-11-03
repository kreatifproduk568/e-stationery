<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,

            'category' => $this->whenLoaded(
                'category',
                fn () => [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ]
            ),

            'brand' => $this->whenLoaded(
                'brand',
                fn () => [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                ]
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
