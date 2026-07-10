<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    public function view(User $user, Session $session): bool
    {
        return $user->id === $session->mentor_id || $user->id === $session->student_id;
    }

    public function update(User $user, Session $session): bool
    {
        return $user->id === $session->mentor_id || $user->id === $session->student_id;
    }

    public function delete(User $user, Session $session): bool
    {
        return ($user->id === $session->mentor_id || $user->id === $session->student_id)
            && $session->status === 'scheduled';
    }
}
