<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'skill'            => new SkillResource($this->whenLoaded('skill')),
            'skill_id'         => $this->skill_id,
            'proficiency_level'=> $this->proficiency_level,
            'is_teaching'      => (bool) $this->is_teaching,
        ];
    }
}
