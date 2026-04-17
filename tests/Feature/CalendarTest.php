<?php

use App\Models\Brand;
use App\Models\Role;
use App\Models\User;
use App\Models\Villa;
use App\Models\VillaUnit;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);

    $role = Role::query()->firstOrCreate(['slug' => 'admin-sales'], ['name' => 'Admin Sales']);
    $user = User::factory()->create();
    $user->roles()->attach($role);

    actingAs($user);
});

test('calendar page renders all villa and resort unit cards', function () {
    $suffix = Str::lower(Str::random(6));

    $brand = Brand::query()->create([
        'name' => 'Kagivilla ' . $suffix,
        'slug' => 'kagivilla-' . $suffix,
    ]);

    $villa = Villa::query()->create([
        'name' => 'Villa Kamela',
        'slug' => 'villa-kamela',
        'location' => 'Cisarua',
        'is_resort' => false,
        'status' => 'active',
    ]);
    $villa->brands()->attach($brand->id);

    VillaUnit::query()->create([
        'villa_id' => $villa->id,
        'unit_name' => 'Villa Kamela',
        'unit_type' => 'private',
        'capacity' => 15,
        'price_weekday' => 1000000,
        'price_semi_weekend' => 1200000,
        'price_weekend' => 1500000,
        'status' => 'active',
    ]);

    $resort = Villa::query()->create([
        'name' => 'Resort Puncak Indah',
        'slug' => 'resort-puncak-indah',
        'location' => 'Megamendung',
        'is_resort' => true,
        'status' => 'active',
    ]);
    $resort->brands()->attach($brand->id);

    VillaUnit::query()->create([
        'villa_id' => $resort->id,
        'unit_name' => 'Unit A',
        'unit_type' => 'family',
        'capacity' => 10,
        'price_weekday' => 800000,
        'price_semi_weekend' => 900000,
        'price_weekend' => 1000000,
        'status' => 'active',
    ]);

    VillaUnit::query()->create([
        'villa_id' => $resort->id,
        'unit_name' => 'Unit B',
        'unit_type' => 'family',
        'capacity' => 8,
        'price_weekday' => 700000,
        'price_semi_weekend' => 800000,
        'price_weekend' => 900000,
        'status' => 'active',
    ]);

    get(route('calendar'))
        ->assertOk()
        ->assertSee('Kalender Booking Semua Villa dan Unit', false)
        ->assertSee('Villa Kamela', false)
        ->assertSee('Resort Puncak Indah - Unit A', false)
        ->assertSee('Resort Puncak Indah - Unit B', false)
        ->assertSee('Ctrl + F', false)
        ->assertDontSee('Booking Bulan Ini', false);
});
