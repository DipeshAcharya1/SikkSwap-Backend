<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSkill;

class UserSkillPolicy
{
    public function update(User $user, UserSkill $userSkill): bool
    {
        return $user->id === $userSkill->user_id;
    }

    public function delete(User $user, UserSkill $userSkill): bool
    {
        return $user->id === $userSkill->user_id;
    }
}
