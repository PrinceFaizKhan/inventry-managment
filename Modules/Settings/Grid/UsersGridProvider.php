<?php

namespace Modules\Settings\Grid;

use App\Models\Role;
use App\Models\User;
use App\Models\User as Actor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Support\Grid\GridDataProvider;

class UsersGridProvider implements GridDataProvider
{
    public function query(array $parameters, array $definition): Builder
    {
        $query = User::query()
            ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.role_id', 'users.created_at')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->with('role:id,name');

        if ($parameters['search'] !== '') {
            $query->where(function (Builder $query) use ($parameters, $definition): void {
                foreach ($definition['searchable'] as $field) {
                    $query->orWhere($field, 'like', "%{$parameters['search']}%");
                }
            });
        }

        if (($roleId = $parameters['filters']['role_id'] ?? null) !== null) {
            $query->where('users.role_id', $roleId);
        }

        if (($status = $parameters['filters']['status'] ?? null) !== null) {
            $query->where('users.status', $status);
        }

        $query->orderBy($definition['sorts'][$parameters['sort']], $parameters['direction'])->orderBy('users.id');

        return $query;
    }

    public function row(Model $model): array
    {
        /** @var User $model */
        return [
            'id' => $model->id,
            'name' => $model->name,
            'email' => $model->email,
            'role' => $model->role?->name,
            'role_id' => $model->role_id,
            'status' => $model->status,
        ];
    }

    public function filterOptions(): array
    {
        return [
            'role_id' => Role::query()->orderBy('name')->get(['id', 'name'])->map(fn (Role $role): array => ['value' => $role->id, 'label' => $role->name])->all(),
            'status' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ],
        ];
    }

    public function acceptsFilterValue(string $filter, mixed $value): bool
    {
        return match ($filter) {
            'role_id' => Role::query()->whereKey($value)->exists(),
            'status' => in_array($value, ['active', 'inactive'], true),
            default => false,
        };
    }

    public function isSelectable(Actor $actor, Model $record): bool
    {
        return $record->getKey() !== $actor->getKey();
    }
}
