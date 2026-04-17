<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilePasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('pages.profile', [
            'title' => 'Profil',
            'user' => $request->user()?->load('roles'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $before = $user->only(['name', 'username', 'email']);
            $user->update($request->validated());

            $this->auditLog(
                module: 'profile',
                action: 'update',
                description: 'User memperbarui profil pribadinya.',
                subject: $user,
                before: $before,
                after: $user->fresh()->only(['name', 'username', 'email']),
            );
        }

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(ProfilePasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! Hash::check($request->string('current_password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini tidak sesuai.',
            ]);
        }

        $user->update([
            'password' => $request->string('password')->toString(),
        ]);

        $this->auditLog(
            module: 'profile',
            action: 'update_password',
            description: 'User mengganti kata sandi akun pribadinya.',
            subject: $user,
            properties: [
                'target' => 'password',
            ],
        );

        return redirect()->route('profile')->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
