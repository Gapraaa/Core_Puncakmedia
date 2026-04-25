<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('guest is redirected to signin when opening dashboard', function () {
    get(route('dashboard'))
        ->assertRedirect(route('signin'));
});

test('user can login using username', function () {
    Role::query()->firstOrCreate([
        'slug' => 'master',
    ], [
        'name' => 'Master',
    ]);

    User::query()->updateOrCreate([
        'username' => 'master',
    ], [
        'name' => 'Master User',
        'email' => 'master@example.com',
        'password' => 'password',
    ]);

    post(route('signin.store'), [
        'login' => 'master',
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
});

test('finance cannot access migration page', function () {
    $financeRole = Role::query()->firstOrCreate([
        'slug' => 'finance',
    ], [
        'name' => 'Finance',
    ]);

    $user = User::factory()->create();
    $user->roles()->attach($financeRole);

    actingAs($user);

    get(route('migration.legacy'))
        ->assertForbidden();
});

test('finance cannot access invoice module', function () {
    $financeRole = Role::query()->firstOrCreate([
        'slug' => 'finance',
    ], [
        'name' => 'Finance',
    ]);

    $user = User::factory()->create();
    $user->roles()->attach($financeRole);

    actingAs($user);

    get(route('invoices.index'))
        ->assertForbidden();
});

test('inactive user cannot login', function () {
    Role::query()->firstOrCreate([
        'slug' => 'master',
    ], [
        'name' => 'Master',
    ]);

    User::query()->updateOrCreate([
        'username' => 'inactive-master',
    ], [
        'name' => 'Inactive Master',
        'email' => 'inactive-master@example.com',
        'password' => 'password',
        'is_active' => false,
    ]);

    post(route('signin.store'), [
        'login' => 'inactive-master',
        'password' => 'password',
    ])->assertSessionHasErrors(['login']);

    $this->assertGuest();
});
