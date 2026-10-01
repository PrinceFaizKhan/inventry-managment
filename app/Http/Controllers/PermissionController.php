<?php

namespace App\Http\Controllers;

use App\Support\Permissions\PermissionRegistry;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function __invoke(PermissionRegistry $permissions): JsonResponse
    {
        return response()->json(['data' => $permissions->tree(), 'count' => count($permissions->keys())]);
    }
}
