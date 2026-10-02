<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkGridActionRequest;
use App\Support\Grid\GridBulkActionHandler;
use App\Support\Grid\GridRegistry;
use App\Support\Menu\MenuAuthorizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BulkGridActionController extends Controller
{
    public function __invoke(BulkGridActionRequest $request, string $grid, string $action, GridRegistry $grids, MenuAuthorizer $authorizer): JsonResponse
    {
        $definition = $grids->find($grid);
        abort_unless($definition['selectable'] && isset($definition['bulk_actions'][$action]), 404);
        abort_unless($authorizer->allows($request->user(), $definition['bulk_actions'][$action]), 403);

        $changes = $request->validated('changes', []);
        if ($action === 'edit_selected') {
            foreach (array_keys($changes) as $field) {
                abort_unless($authorizer->allows($request->user(), $definition['bulk_edit_fields'][$field]['permission']), 403);
            }
            if (($changes['status'] ?? null) === 'inactive') {
                abort_unless(isset($definition['bulk_actions']['deactivate_selected']) && $authorizer->allows($request->user(), $definition['bulk_actions']['deactivate_selected']), 403);
            }
            if (($changes['status'] ?? null) === 'active') {
                abort_unless(isset($definition['bulk_actions']['activate_selected']) && $authorizer->allows($request->user(), $definition['bulk_actions']['activate_selected']), 403);
            }
        }

        $handler = app($definition['bulk_handler']);
        if (! $handler instanceof GridBulkActionHandler) {
            throw ValidationException::withMessages(['action' => 'The configured bulk-action handler is invalid.']);
        }

        $updated = $handler->handle($request->user(), $action, $request->validated('ids'), $changes);
        $message = match ($action) {
            'activate_selected' => "{$updated} selected ".str('record')->plural($updated).' activated.',
            'deactivate_selected' => "{$updated} selected ".str('record')->plural($updated).' deactivated.',
            'delete_selected' => "{$updated} selected ".str('record')->plural($updated).' deleted.',
            default => "{$updated} selected ".str('record')->plural($updated).' updated.',
        };

        return response()->json(['updated' => $updated, 'message' => $message]);
    }
}
