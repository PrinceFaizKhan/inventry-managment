<?php

namespace Modules\Admin\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Settings\DataGrids\UserDataGrid;

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

        $targetStatus = $user->status === 'active' ? 'inactive' : 'active';

        // Cannot deactivate own account
        if ($user->is($actor) && $targetStatus === 'inactive') {
            return response()->json([
                'message' => 'You cannot deactivate your own account.',
            ], 422);
        }

        // Last active Super Admin protection
        if ($targetStatus === 'inactive') {
            $isSuperAdmin = $user->role && mb_strtolower((string) $user->role->name) === 'super admin';
            if ($isSuperAdmin) {
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
            }
        }

        $user->update(['status' => $targetStatus]);

        $label = $targetStatus === 'active' ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "User {$user->name} {$label} successfully.",
            'status' => $targetStatus,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $targetStatus,
            ],
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
            if ($user->is($actor)) {
                return response()->json([
                    'message' => 'You cannot deactivate your own account.',
                ], 422);
            }

            $isSuperAdmin = $user->role && mb_strtolower((string) $user->role->name) === 'super admin';
            if ($isSuperAdmin) {
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
            }
        }

        $user->update($validated);

        return response()->json([
            'message' => "User {$user->name} updated successfully.",
            'user' => $user,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor) {
            abort(401);
        }

        if ($user->is($actor)) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $isSuperAdmin = $user->role && mb_strtolower((string) $user->role->name) === 'super admin';
        if ($isSuperAdmin) {
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
        }

        $user->delete();

        return response()->json([], 204);
    }
}
