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

test('master can open user management page', function () {
    $role = Role::query()->firstOrCreate(['slug' => 'master'], ['name' => 'Master']);
    $user = User::factory()->create();
    $user->roles()->attach($role);

    actingAs($user);

    get(route('users.index'))
        ->assertOk()
        ->assertSee('Manajemen User', false);
});

test('superadmin can create internal user', function () {
    $superadminRole = Role::query()->firstOrCreate(['slug' => 'superadmin'], ['name' => 'Superadmin']);
    $financeRole = Role::query()->firstOrCreate(['slug' => 'finance'], ['name' => 'Finance']);

    $user = User::factory()->create();
    $user->roles()->attach($superadminRole);

    actingAs($user);

    post(route('users.store'), [
        'name' => 'Finance Operasional',
        'username' => 'financeops',
        'email' => 'financeops@example.com',
        'password' => 'password',
        'is_active' => 1,
        'role_ids' => [$financeRole->id],
    ])->assertRedirect(route('users.index'));

    $createdUser = User::query()->where('username', 'financeops')->first();

    expect($createdUser)->not->toBeNull();
    expect($createdUser->roles->pluck('slug')->all())->toContain('finance');
    expect($createdUser->is_active)->toBeTrue();
});

test('finance cannot open user management page', function () {
    $financeRole = Role::query()->firstOrCreate(['slug' => 'finance'], ['name' => 'Finance']);
    $user = User::factory()->create();
    $user->roles()->attach($financeRole);

    actingAs($user);

    get(route('users.index'))
        ->assertForbidden();
});

test('master can update user status and password', function () {
    $masterRole = Role::query()->firstOrCreate(['slug' => 'master'], ['name' => 'Master']);
    $financeRole = Role::query()->firstOrCreate(['slug' => 'finance'], ['name' => 'Finance']);

    $masterUser = User::factory()->create();
    $masterUser->roles()->attach($masterRole);

    $managedUser = User::factory()->create([
        'password' => 'password',
        'is_active' => true,
    ]);
    $managedUser->roles()->attach($financeRole);

    actingAs($masterUser);

    post(route('users.update', $managedUser), [
        '_method' => 'PUT',
        'name' => $managedUser->name,
        'username' => $managedUser->username,
        'email' => $managedUser->email,
        'password' => 'newsecret',
        'is_active' => 0,
        'role_ids' => [$financeRole->id],
    ])->assertRedirect(route('users.index'));

    $managedUser->refresh();

    expect($managedUser->is_active)->toBeFalse();
    expect(\Illuminate\Support\Facades\Hash::check('newsecret', $managedUser->password))->toBeTrue();
});
