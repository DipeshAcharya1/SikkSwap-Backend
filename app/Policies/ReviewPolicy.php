<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function delete(User $user, Review $review): bool
    {
        return $user->role === 'admin' || $user->id === $review->reviewer_id;
    }
}
