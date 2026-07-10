<?php

namespace App\Policies;

use App\Models\Skill;
use App\Models\User;

class SkillPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Skill $skill): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Skill $skill): bool
    {
        return $user->role === 'admin';
    }
}
