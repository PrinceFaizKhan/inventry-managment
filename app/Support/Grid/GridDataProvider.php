<?php

namespace App\Support\Grid;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface GridDataProvider
{
    /** @param array<string, mixed> $parameters */
    public function query(array $parameters, array $definition): Builder;

    /** @return array<string, mixed> */
    public function row(Model $model): array;

    /** @return array<string, array<int, array{value: string|int, label: string}>> */
    public function filterOptions(): array;

    public function acceptsFilterValue(string $filter, mixed $value): bool;

    public function isSelectable(User $actor, Model $record): bool;
}
