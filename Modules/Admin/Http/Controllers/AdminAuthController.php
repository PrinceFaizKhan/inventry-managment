<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Admin\Http\Requests\AdminLoginRequest;
use Modules\Admin\Http\Resources\AdminUserResource;

class AdminAuthController extends Controller
{
    /**
     * Authenticate an administrator session.
     */
    public function store(AdminLoginRequest $request): JsonResponse|RedirectResponse
    {
        if (Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'user' => new AdminUserResource($request->user()),
                ]);
            }

            return redirect()->intended('/dashboard');
        }

        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('admin::app.invalid_credentials', [], app()->getLocale()) ?: 'The provided credentials are incorrect.',
            ]);
        }

        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json([
                'user' => new AdminUserResource($request->user()),
            ]);
        }

        return redirect()->intended('/dashboard');
    }

    /**
     * Bootstrap the currently authenticated administrator.
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user ? new AdminUserResource($user) : null,
        ]);
    }

    /**
     * Terminate the administrator session.
     */
    public function destroy(Request $request): Response|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect('/');
    }
}
