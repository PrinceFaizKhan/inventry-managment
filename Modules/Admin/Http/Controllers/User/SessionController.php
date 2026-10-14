<?php

namespace Modules\Admin\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Admin\Http\Requests\AdminLoginRequest;
use Modules\Admin\Http\Resources\AdminUserResource;

class SessionController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard.index');
        }

        return view('admin::users.sessions.create');
    }

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

            return redirect()->intended(route('admin.dashboard.index'));
        }

        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($request->wantsJson()) {
                throw ValidationException::withMessages([
                    'email' => __('admin::app.invalid_credentials', [], app()->getLocale()) ?: 'The provided credentials are incorrect.',
                ]);
            }

            return back()->withInput($request->only('email', 'remember'))->withErrors([
                'email' => __('admin::app.invalid_credentials', [], app()->getLocale()) ?: 'The provided credentials are incorrect.',
            ]);
        }

        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json([
                'user' => new AdminUserResource($request->user()),
            ]);
        }

        return redirect()->intended(route('admin.dashboard.index'));
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
}
