<?php

namespace Modules\Admin\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
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

    public function store(StoreRoleRequest $request, \Modules\Settings\Roles\Http\Controllers\RoleController $roleController): JsonResponse
    {
        return $roleController->store($request);
    }
}
