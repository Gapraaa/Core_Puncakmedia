<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('authenticated user can open profile page', function () {
    $role = Role::query()->firstOrCreate(['slug' => 'admin-sales'], ['name' => 'Admin Sales']);
    $user = User::factory()->create();
    $user->roles()->attach($role);

    actingAs($user);

    get(route('profile'))
        ->assertOk()
        ->assertSee('Profil Saya', false)
        ->assertSee($user->username, false);
});

test('authenticated user can update own profile', function () {
    $role = Role::query()->firstOrCreate(['slug' => 'admin-sales'], ['name' => 'Admin Sales']);
    $user = User::factory()->create([
        'name' => 'Sales Lama',
        'username' => 'saleslama',
        'email' => 'saleslama@example.com',
    ]);
    $user->roles()->attach($role);

    actingAs($user);

    put(route('profile.update'), [
        'name' => 'Sales Baru',
        'username' => 'salesbaru',
        'email' => 'salesbaru@example.com',
    ])->assertRedirect(route('profile'));

    $user->refresh();

    expect($user->name)->toBe('Sales Baru');
    expect($user->username)->toBe('salesbaru');
    expect($user->email)->toBe('salesbaru@example.com');
});

test('authenticated user can change own password', function () {
    $role = Role::query()->firstOrCreate(['slug' => 'finance'], ['name' => 'Finance']);
    $user = User::factory()->create([
        'password' => 'password',
    ]);
    $user->roles()->attach($role);

    actingAs($user);

    put(route('profile.password.update'), [
        'current_password' => 'password',
        'password' => 'newsecret',
        'password_confirmation' => 'newsecret',
    ])->assertRedirect(route('profile'));

    $user->refresh();

    expect(Hash::check('newsecret', $user->password))->toBeTrue();
});
