<?php

namespace App\Support\Menu;

use App\Models\User;
use App\Support\Permissions\PermissionRegistry;
use ArrayObject;

class MenuAuthorizer
{
    public function __construct(private readonly PermissionRegistry $permissions) {}

    public function allows(User $user, ?string $permission): bool
    {
        if ($permission === null) {
            return true;
        }

        if ($permission === 'authenticated') {
            return true;
        }

        if (! $this->permissions->has($permission)) {
            return false;
        }

        $role = $user->role;
        if ($role === null) {
            return false;
        }
        if ($role->access === 'administration') {
            return true;
        }
        if ($role->access !== 'custom') {
            return false;
        }

        $granted = $role->permissions;
        $granted = $granted instanceof ArrayObject ? $granted->getArrayCopy() : (array) $granted;

        return in_array($permission, $granted, true);
    }
}
