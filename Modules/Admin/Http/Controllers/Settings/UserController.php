<?php

namespace Modules\Admin\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\Settings\StoreUserRequest;
use Modules\Admin\Http\Requests\Settings\UpdateUserRequest;
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

    public function create(): View
    {
        $roles = Role::query()->select(['id', 'name'])->orderBy('name')->get();

        return view('admin::settings.users.create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => $validated['role_id'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        $user->loadMissing('role:id,name');

        if ($request->wantsJson()) {
            return (new UserResource($user))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()
            ->route('settings.users')
            ->with('success', "User {$user->name} created successfully.");
    }

    public function edit(User $user): View
    {
        $user->loadMissing('role:id,name');
        $roles = Role::query()->select(['id', 'name'])->orderBy('name')->get();

        return view('admin::settings.users.edit', [
            'user' => (new UserResource($user))->resolve(),
            'roles' => $roles,
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $user->loadMissing('role:id,name');

        return (new UserResource($user))->response();
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

    public function update(UpdateUserRequest $request, User $user): JsonResponse|RedirectResponse
    {
        $actor = $request->user();
        if (! $actor) {
            abort(401);
        }

        $validated = $request->validated();

        if (isset($validated['status']) && $validated['status'] === 'inactive') {
            if ($errorResponse = $this->ensureNotSelf($user, $actor, 'deactivate')) {
                return $errorResponse;
            }

            if ($errorResponse = $this->ensureNotLastSuperAdmin($user)) {
                return $errorResponse;
            }
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->loadMissing('role:id,name');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "User {$user->name} updated successfully.",
                'user' => (new UserResource($user))->resolve(),
            ]);
        }

        return redirect()
            ->route('settings.users')
            ->with('success', "User {$user->name} updated successfully.");
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
