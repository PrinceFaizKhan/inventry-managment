<?php

namespace App\Http\Controllers;

use App\Support\Menu\MenuRegistry;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function __invoke(Request $request, MenuRegistry $menus)
    {
        return response()->json(['data' => $menus->for($request->user())]);
    }
}
