<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Core\Support\Permissions\PermissionRegistry;

class PermissionController extends Controller
{
    public function __invoke(PermissionRegistry $permissions): JsonResponse
    {
        return response()->json(['data' => $permissions->tree(), 'count' => count($permissions->keys())]);
    }
}
