<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'status'     => $this->status,
            'message'    => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'student'    => new UserResource($this->whenLoaded('student')),
            'mentor'     => new UserResource($this->whenLoaded('mentor')),
            'skill'      => new SkillResource($this->whenLoaded('skill')),
        ];
    }
}
