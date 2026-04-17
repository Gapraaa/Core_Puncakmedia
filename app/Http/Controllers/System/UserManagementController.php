<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagementRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('roles')
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));

                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('username', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('role'), function (Builder $query) use ($request): void {
                $roleSlug = trim((string) $request->string('role'));

                $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('slug', $roleSlug));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.users.index', [
            'title' => 'Manajemen User',
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(),
            'filters' => $request->only(['q', 'role']),
        ]);
    }

    public function create(): View
    {
        return view('pages.users.create', [
            'title' => 'Tambah User',
            'user' => new User(),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(UserManagementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => (bool) $data['is_active'],
        ]);

        $user->roles()->sync($data['role_ids']);

        $this->auditLog(
            module: 'users',
            action: 'create',
            description: 'User internal baru dibuat.',
            subject: $user,
            after: [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'roles' => $user->roles()->pluck('slug')->all(),
            ],
        );

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('pages.users.edit', [
            'title' => 'Edit User',
            'user' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UserManagementRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $before = [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'roles' => $user->roles()->pluck('slug')->all(),
        ];

        $payload = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'is_active' => (bool) $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);
        $user->roles()->sync($data['role_ids']);

        $this->auditLog(
            module: 'users',
            action: 'update',
            description: 'Data user internal diperbarui.',
            subject: $user,
            before: $before,
            after: [
                'name' => $user->fresh()->name,
                'username' => $user->fresh()->username,
                'email' => $user->fresh()->email,
                'is_active' => $user->fresh()->is_active,
                'roles' => $user->fresh()->roles()->pluck('slug')->all(),
            ],
            properties: [
                'password_reset' => ! empty($data['password']) ? 'ya' : 'tidak',
            ],
        );

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Kamu tidak bisa menghapus akun yang sedang dipakai.');
        }

        $before = [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'roles' => $user->roles()->pluck('slug')->all(),
        ];

        $user->delete();

        $this->auditLog(
            module: 'users',
            action: 'delete',
            description: 'User internal dihapus dari sistem.',
            subject: $user,
            before: $before,
        );

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
