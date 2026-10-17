<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Attribute\Models\Attribute;

class DashboardController extends Controller
{
    /**
     * Display the Admin Dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $stats = [
            'total_attributes' => class_exists(Attribute::class) ? Attribute::query()->count() : 0,
            'total_users' => User::query()->count(),
            'total_roles' => Role::query()->count(),
            'filterable_attributes' => class_exists(Attribute::class) ? Attribute::query()->where('is_filterable', true)->count() : 0,
        ];

        if ($request->wantsJson()) {
            return response()->json(['data' => $stats]);
        }

        return view('admin::dashboard.index', compact('stats'));
    }
}
