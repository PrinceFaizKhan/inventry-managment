<?php

namespace Modules\Admin\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Modules\Settings\Roles\Http\Requests\StoreRoleRequest;

class RoleController extends Controller
{
    public function create(): View
    {
        if (! auth()->check()) {
            return view('app');
        }

        return view('admin::settings.roles.create');
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $permissions = $data['access'] === 'administration' ? [] : array_values(array_unique($data['permissions'] ?? []));

        $role = Role::query()->create([
            'name' => $data['name'],
            'access' => $data['access'],
            'permissions' => $permissions,
        ]);

        return response()->json(['data' => $role], 201);
    }
}
