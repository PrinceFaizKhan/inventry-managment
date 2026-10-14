<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Support\Menu\MenuRegistry;

class NavigationController extends Controller
{
    public function __invoke(Request $request, MenuRegistry $menus): JsonResponse
    {
        return response()->json(['data' => $menus->for($request->user())]);
    }
}
