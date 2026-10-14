<?php

namespace Modules\Settings\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Support\Grid\GridBulkActionHandler;

class UsersBulkActions implements GridBulkActionHandler
{
    public function supportedActions(): array
    {
        return ['activate_selected', 'deactivate_selected', 'edit_selected', 'delete_selected'];
    }

    public function editableFields(): array
    {
        return ['status', 'role_id'];
    }

    public function handle(User $actor, string $action, array $ids, array $changes): int
    {
        return DB::transaction(function () use ($actor, $action, $ids, $changes): int {
            $users = User::query()->whereKey($ids)->with('role')->lockForUpdate()->get();
            if ($users->count() !== count($ids)) {
                throw ValidationException::withMessages(['ids' => 'One or more selected users are no longer available.']);
            }

            if ($users->contains(fn (User $user): bool => $user->is($actor))) {
                throw ValidationException::withMessages(['ids' => 'Your account cannot be included in a bulk action.']);
            }

            $this->protectActor($actor, $action, $users, $changes);
            $this->protectLastAdministrator($action, $users, $changes);

            return match ($action) {
                'activate_selected' => User::query()->whereKey($ids)->update(['status' => 'active']),
                'deactivate_selected' => User::query()->whereKey($ids)->update(['status' => 'inactive']),
                'edit_selected' => User::query()->whereKey($ids)->update($changes),
                'delete_selected' => User::query()->whereKey($ids)->delete(),
                default => throw ValidationException::withMessages(['action' => 'This bulk action is not supported.']),
            };
        });
    }

    /** @param EloquentCollection<int, User> $users @param array<string, mixed> $changes */
    private function protectActor(User $actor, string $action, EloquentCollection $users, array $changes): void
    {
        $affectsStatus = in_array($action, ['deactivate_selected', 'delete_selected'], true)
            || ($action === 'edit_selected' && ($changes['status'] ?? null) === 'inactive');

        if ($affectsStatus && $users->contains(fn (User $user): bool => $user->is($actor))) {
            throw ValidationException::withMessages(['ids' => 'You cannot deactivate or delete your own account.']);
        }
    }

    /** @param EloquentCollection<int, User> $users @param array<string, mixed> $changes */
    private function protectLastAdministrator(string $action, EloquentCollection $users, array $changes): void
    {
        $removesAdministrator = in_array($action, ['deactivate_selected', 'delete_selected'], true)
            || ($action === 'edit_selected' && (($changes['status'] ?? null) === 'inactive' || array_key_exists('role_id', $changes)));

        if (! $removesAdministrator) {
            return;
        }

        $activeAdministrators = User::query()
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->whereRaw('LOWER(name) = ?', ['super admin']))
            ->lockForUpdate()
            ->get();

        if ($activeAdministrators->isEmpty()) {
            return;
        }

        $changedRole = array_key_exists('role_id', $changes)
            ? ($changes['role_id'] === null ? null : User::query()->getModel()->getConnection()->table('roles')->where('id', $changes['role_id'])->value('name'))
            : null;

        $remaining = $activeAdministrators->reject(function (User $administrator) use ($action, $users, $changes, $changedRole): bool {
            if (! $users->contains(fn (User $user): bool => $user->is($administrator))) {
                return false;
            }

            if (in_array($action, ['deactivate_selected', 'delete_selected'], true) || ($changes['status'] ?? null) === 'inactive') {
                return true;
            }

            return array_key_exists('role_id', $changes) && mb_strtolower((string) $changedRole) !== 'super admin';
        });

        if ($remaining->isEmpty()) {
            throw ValidationException::withMessages(['ids' => 'At least one active Super Admin must remain.']);
        }
    }
}
