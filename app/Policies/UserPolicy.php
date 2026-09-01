<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users.manage');
    }

    public function view(User $actor, User $subject): bool
    {
        return ! $subject->isGodfather()
            && $actor->business_id === $subject->business_id
            && $actor->can('users.manage');
    }

    public function update(User $actor, User $subject): bool
    {
        return $this->view($actor, $subject);
    }

    public function delete(User $actor, User $subject): bool
    {
        return $this->view($actor, $subject);
    }
}
