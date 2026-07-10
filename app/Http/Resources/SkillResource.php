<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'category_id' => $this->category_id,
            'mentors_count' => $this->when(isset($this->mentors_count), $this->mentors_count),
        ];
    }
}
