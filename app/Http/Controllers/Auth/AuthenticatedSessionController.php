<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();

            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            if ($user) {
                // 1) Prefer helper methods if they exist
                if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }

                if (method_exists($user, 'isConsultant') && $user->isConsultant()) {
                    return redirect()->route('admin.dashboard');
                }

                // 2) Fallback: direct role / flag check
                if (
                    (isset($user->role) && in_array($user->role, ['admin', 'consultant'], true)) ||
                    (!isset($user->role) && ($user->is_admin ?? false))
                ) {
                    return redirect()->route('admin.dashboard');
                }
            }

            // Default for normal users
            return redirect('/');
        } catch (ValidationException $e) {
            return redirect()->to('/?login=1')
                ->withErrors($e->errors())
                ->withInput()
                ->with('openLogin', true);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
