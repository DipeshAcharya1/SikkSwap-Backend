<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'name'                     => $this->name,
            'email'                    => $this->email,
            'role'                     => $this->role,
            'profile_image'            => $this->profile_image,
            'bio'                      => $this->bio,
            'created_at'               => $this->created_at,
            'updated_at'               => $this->updated_at,
            // Included when mentor listing loads userSkills
            'user_skills'              => UserSkillResource::collection($this->whenLoaded('userSkills')),
            'teaching_count'           => $this->when(isset($this->teaching_count), $this->teaching_count),
            'completed_sessions_count' => $this->when(isset($this->completed_sessions_count), $this->completed_sessions_count),
        ];
    }
}
