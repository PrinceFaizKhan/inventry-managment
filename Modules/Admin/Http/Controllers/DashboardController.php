<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Attribute;
use Modules\Inventory\Providers\InventoryServiceProvider;
use Modules\Sales\Providers\SalesServiceProvider;

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
            'connectors' => [
                'sales' => [
                    'name' => 'Sales Connector',
                    'installed' => class_exists(SalesServiceProvider::class),
                    'status' => 'Pluggable External Module',
                ],
                'inventory' => [
                    'name' => 'Inventory Connector',
                    'installed' => class_exists(InventoryServiceProvider::class),
                    'status' => 'Pluggable External Module',
                ],
            ],
        ];

        if ($request->wantsJson()) {
            return response()->json(['data' => $stats]);
        }

        return view('admin::dashboard.index', compact('stats'));
    }
}
