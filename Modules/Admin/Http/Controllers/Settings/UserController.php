<?php

namespace Modules\Admin\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        if (! auth()->check()) {
            return view('app');
        }

        return view('admin::settings.users.index');
    }
}
