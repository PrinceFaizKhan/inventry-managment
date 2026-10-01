<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        return response()->json(['data' => User::query()->select('id', 'name', 'email', 'created_at')->orderBy('name')->get()]);
    }
}
