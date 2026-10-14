<?php

namespace Modules\Core\Support\Grid;

use App\Models\User;

interface GridBulkActionHandler
{
    /** @return list<string> */
    public function supportedActions(): array;

    /** @return list<string> */
    public function editableFields(): array;

    /** @param list<int> $ids @param array<string, mixed> $changes */
    public function handle(User $actor, string $action, array $ids, array $changes): int;
}
