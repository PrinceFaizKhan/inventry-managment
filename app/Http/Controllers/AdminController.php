<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => User::query()->select('id', 'name', 'email', 'created_at')->orderBy('name')->get()]);
    }
}
