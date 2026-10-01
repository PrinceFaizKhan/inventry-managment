<?php

namespace App\Http\Controllers;

use App\Support\Menu\MenuRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function __invoke(Request $request, MenuRegistry $menus): JsonResponse
    {
        return response()->json(['data' => $menus->for($request->user())]);
    }
}
