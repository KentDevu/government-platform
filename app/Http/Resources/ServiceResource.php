<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'title' => $this->title,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'department_id' => $this->department_id,
            'description' => $this->description,
            'cta' => $this->cta,
            'color' => $this->color,
            'url' => $this->url,
            'page' => $this->page,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
