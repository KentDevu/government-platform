<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'category_color' => $this->category_color,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'date' => $this->date,
            'image' => $this->image,
            'image_alt' => $this->image_alt,
            'sort_order' => $this->sort_order,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
