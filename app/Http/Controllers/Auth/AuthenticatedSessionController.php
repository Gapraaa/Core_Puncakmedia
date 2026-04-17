<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('pages.auth.signin', [
            'title' => 'Masuk',
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $login = trim((string) $request->string('login'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([
            $field => $login,
            'password' => $request->string('password')->toString(),
        ], $request->boolean('remember'))) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'login' => 'Username, email, atau kata sandi tidak sesuai.',
                ]);
        }

        $request->session()->regenerate();

        if (! $request->user()?->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'login' => 'Akun kamu sedang nonaktif. Hubungi Master atau Superadmin.',
                ]);
        }

        $this->auditLog(
            module: 'auth',
            action: 'login',
            description: 'User berhasil masuk ke sistem.',
            subject: $request->user(),
            after: [
                'username' => $request->user()?->username,
                'email' => $request->user()?->email,
            ],
        );

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $this->auditLog(
                module: 'auth',
                action: 'logout',
                description: 'User keluar dari sistem.',
                subject: $user,
                after: [
                    'username' => $user->username,
                    'email' => $user->email,
                ],
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin')->with('success', 'Kamu berhasil keluar dari sistem.');
    }
}
