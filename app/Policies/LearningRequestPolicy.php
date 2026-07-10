<?php

namespace App\Policies;

use App\Models\LearningRequest;
use App\Models\User;

class LearningRequestPolicy
{
    public function view(User $user, LearningRequest $learningRequest): bool
    {
        return $user->id === $learningRequest->student_id
            || $user->id === $learningRequest->mentor_id;
    }

    /** Only the mentor can accept/reject */
    public function update(User $user, LearningRequest $learningRequest): bool
    {
        return $user->id === $learningRequest->mentor_id
            && $learningRequest->status === 'pending';
    }

    /** Only the student can cancel */
    public function delete(User $user, LearningRequest $learningRequest): bool
    {
        return $user->id === $learningRequest->student_id
            && $learningRequest->status === 'pending';
    }
}
