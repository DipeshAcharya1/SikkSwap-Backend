<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'status'       => $this->status,
            'start_time'   => $this->start_time,
            'end_time'     => $this->end_time,
            'meeting_link' => $this->meeting_link,
            'created_at'   => $this->created_at,
            'mentor'       => new UserResource($this->whenLoaded('mentor')),
            'student'      => new UserResource($this->whenLoaded('student')),
            'skill'        => new SkillResource($this->whenLoaded('skill')),
            'review'       => new ReviewResource($this->whenLoaded('review')),
        ];
    }
}
