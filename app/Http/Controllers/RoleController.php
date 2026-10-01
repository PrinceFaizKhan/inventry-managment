<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
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
