<?php

namespace Modules\Admin\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Settings\DataGrids\UserDataGrid;
use Modules\Settings\Users\Http\Resources\UserResource;

class UserController extends Controller
{
    public function index(): View|JsonResponse
    {
        if (! auth()->check()) {
            return view('app');
        }

        return UserDataGrid::make()->render('admin::settings.users.index');
    }

    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor) {
            abort(401);
        }

        $requestedStatus = $request->input('status');
        if (! $requestedStatus && str_ends_with($request->path(), '/activate')) {
            $requestedStatus = 'active';
        } elseif (! $requestedStatus && str_ends_with($request->path(), '/deactivate')) {
            $requestedStatus = 'inactive';
        }

        $targetStatus = in_array($requestedStatus, ['active', 'inactive'], true)
            ? $requestedStatus
            : ($user->status === 'active' ? 'inactive' : 'active');

        if ($targetStatus === 'inactive') {
            if ($errorResponse = $this->ensureNotSelf($user, $actor, 'deactivate')) {
                return $errorResponse;
            }

            if ($errorResponse = $this->ensureNotLastSuperAdmin($user)) {
                return $errorResponse;
            }
        }

        $user->update(['status' => $targetStatus]);

        $label = $targetStatus === 'active' ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "User {$user->name} {$label} successfully.",
            'status' => $targetStatus,
            'user' => (new UserResource($user))->resolve(),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor) {
            abort(401);
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'name' => ['sometimes', 'string', 'max:255'],
            'role_id' => ['sometimes', 'nullable', 'exists:roles,id'],
        ]);

        if (isset($validated['status']) && $validated['status'] === 'inactive') {
            if ($errorResponse = $this->ensureNotSelf($user, $actor, 'deactivate')) {
                return $errorResponse;
            }

            if ($errorResponse = $this->ensureNotLastSuperAdmin($user)) {
                return $errorResponse;
            }
        }

        $user->update($validated);

        return response()->json([
            'message' => "User {$user->name} updated successfully.",
            'user' => (new UserResource($user))->resolve(),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor) {
            abort(401);
        }

        if ($errorResponse = $this->ensureNotSelf($user, $actor, 'delete')) {
            return $errorResponse;
        }

        if ($errorResponse = $this->ensureNotLastSuperAdmin($user)) {
            return $errorResponse;
        }

        $user->delete();

        return response()->json([], 204);
    }

    /**
     * Prevent self-deactivation or self-deletion.
     */
    private function ensureNotSelf(User $user, User $actor, string $action): ?JsonResponse
    {
        if ($user->is($actor)) {
            return response()->json([
                'message' => "You cannot {$action} your own account.",
            ], 422);
        }

        return null;
    }

    /**
     * Ensure at least one active Super Admin remains.
     */
    private function ensureNotLastSuperAdmin(User $user): ?JsonResponse
    {
        $isSuperAdmin = $user->role && mb_strtolower((string) $user->role->name) === 'super admin';
        if (! $isSuperAdmin) {
            return null;
        }

        $otherActiveSuperAdmins = User::query()
            ->where('id', '!=', $user->id)
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->whereRaw('LOWER(name) = ?', ['super admin']))
            ->count();

        if ($otherActiveSuperAdmins === 0) {
            return response()->json([
                'message' => 'At least one active Super Admin must remain.',
            ], 422);
        }

        return null;
    }
}
