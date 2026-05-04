<?php

use App\Jobs\ProcessVillaImage;
use App\Models\Role;
use App\Models\User;
use App\Models\Villa;
use App\Models\VillaImage;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);

    $role = Role::query()->firstOrCreate([
        'slug' => 'master',
    ], [
        'name' => 'Master',
    ]);

    $user = User::factory()->create();
    $user->roles()->attach($role);

    actingAs($user);

    Storage::fake('public');
    Queue::fake();
});

test('villa gallery upload creates image rows and dispatches processing job', function () {
    $villa = Villa::query()->create([
        'name' => 'Villa Gallery Test',
        'slug' => 'villa-gallery-test',
        'location' => 'Puncak',
        'is_resort' => false,
        'status' => 'active',
    ]);

    post(route('villas.images.store', $villa), [
        'images' => [
            UploadedFile::fake()->image('cover-test.jpg', 1600, 900),
            UploadedFile::fake()->image('gallery-test.png', 1400, 900),
        ],
    ])->assertRedirect();

    $villa->refresh();

    expect($villa->images()->count())->toBe(2);
    expect($villa->images()->where('is_cover', true)->count())->toBe(1);
    expect($villa->images()->orderBy('sort_order')->pluck('sort_order')->all())->toBe([1, 2]);

    Queue::assertPushed(ProcessVillaImage::class, 2);
});

test('villa gallery cover and order can be updated', function () {
    $villa = Villa::query()->create([
        'name' => 'Villa Order Test',
        'slug' => 'villa-order-test',
        'location' => 'Puncak',
        'is_resort' => false,
        'status' => 'active',
    ]);

    $first = VillaImage::query()->create([
        'villa_id' => $villa->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'disk' => 'public',
        'original_path' => 'villas/'.$villa->id.'/images/one/original.jpg',
        'original_name' => 'one.jpg',
        'status' => 'ready',
        'sort_order' => 1,
        'is_cover' => true,
    ]);

    $second = VillaImage::query()->create([
        'villa_id' => $villa->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'disk' => 'public',
        'original_path' => 'villas/'.$villa->id.'/images/two/original.jpg',
        'original_name' => 'two.jpg',
        'status' => 'ready',
        'sort_order' => 2,
        'is_cover' => false,
    ]);

    patch(route('villas.images.cover', [$villa, $second]))->assertRedirect();
    patch(route('villas.images.reorder', $villa), [
        'ordered_image_ids' => [$second->id, $first->id],
    ])->assertRedirect();

    expect($villa->images()->where('is_cover', true)->first()?->id)->toBe($second->id);
    expect($villa->images()->orderBy('sort_order')->pluck('id')->all())->toBe([$second->id, $first->id]);
});
