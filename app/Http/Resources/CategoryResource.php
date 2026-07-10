<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'skills_count' => $this->whenLoaded('skills', fn() => $this->skills->count()),
            'skills'     => SkillResource::collection($this->whenLoaded('skills')),
        ];
    }
}
