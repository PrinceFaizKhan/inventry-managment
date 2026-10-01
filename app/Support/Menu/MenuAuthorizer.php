<?php

namespace App\Support\Menu;

use App\Models\User;

class MenuAuthorizer
{
    public function allows(User $user, ?string $permission): bool
    {
        if ($permission === null) {
            return true;
        }

        if ($permission === 'authenticated') {
            return true;
        }

        // Roles and permissions will plug in here. Unknown requirements stay hidden.
        return false;
    }
}
