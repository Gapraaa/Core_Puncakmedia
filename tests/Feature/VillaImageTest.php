<?php

use App\Jobs\ProcessVillaImage;
use App\Models\Role;
use App\Models\User;
use App\Models\Villa;
use App\Models\VillaImage;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
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
    expect($villa->images()->orderBy('sort_order')->pluck('original_name')->all())->toBe([
        'villa-gallery-test-01.jpg',
        'villa-gallery-test-02.png',
    ]);

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

test('villa gallery reorder must include all unique image ids from the same villa', function () {
    $villa = Villa::query()->create([
        'name' => 'Villa Validation Test',
        'slug' => 'villa-validation-test',
        'location' => 'Puncak',
        'is_resort' => false,
        'status' => 'active',
    ]);

    $first = VillaImage::query()->create([
        'villa_id' => $villa->id,
        'uuid' => (string) Str::uuid(),
        'disk' => 'public',
        'original_path' => 'villas/'.$villa->id.'/images/one/villa-validation-test-01.jpg',
        'original_name' => 'villa-validation-test-01.jpg',
        'status' => 'ready',
        'sort_order' => 1,
        'is_cover' => true,
    ]);

    $second = VillaImage::query()->create([
        'villa_id' => $villa->id,
        'uuid' => (string) Str::uuid(),
        'disk' => 'public',
        'original_path' => 'villas/'.$villa->id.'/images/two/villa-validation-test-02.jpg',
        'original_name' => 'villa-validation-test-02.jpg',
        'status' => 'ready',
        'sort_order' => 2,
        'is_cover' => false,
    ]);

    patch(route('villas.images.reorder', $villa), [
        'ordered_image_ids' => [$second->id, $second->id],
    ])->assertSessionHasErrors(['ordered_image_ids.0', 'ordered_image_ids.1']);

    expect($villa->images()->orderBy('sort_order')->pluck('id')->all())->toBe([$first->id, $second->id]);
});

test('failed villa image can be retried and re-queued', function () {
    $villa = Villa::query()->create([
        'name' => 'Villa Retry Test',
        'slug' => 'villa-retry-test',
        'location' => 'Puncak',
        'is_resort' => false,
        'status' => 'active',
    ]);

    Storage::disk('public')->put("villas/{$villa->id}/images/retry/original.jpg", 'fake-image-content');
    Storage::disk('public')->put("villas/{$villa->id}/images/retry/main.webp", 'fake-main-webp');
    Storage::disk('public')->put("villas/{$villa->id}/images/retry/thumb.webp", 'fake-thumb-webp');

    $image = VillaImage::query()->create([
        'villa_id' => $villa->id,
        'uuid' => (string) Str::uuid(),
        'disk' => 'public',
        'original_path' => "villas/{$villa->id}/images/retry/original.jpg",
        'webp_path' => "villas/{$villa->id}/images/retry/main.webp",
        'thumb_path' => "villas/{$villa->id}/images/retry/thumb.webp",
        'original_name' => 'villa-retry-test-01.jpg',
        'status' => 'failed',
        'sort_order' => 1,
        'is_cover' => true,
    ]);

    patch(route('villas.images.retry', [$villa, $image]))->assertRedirect();

    $image->refresh();

    expect($image->status)->toBe('pending')
        ->and($image->webp_path)->toBeNull()
        ->and($image->thumb_path)->toBeNull();

    Storage::disk('public')->assertExists("villas/{$villa->id}/images/retry/original.jpg");
    Storage::disk('public')->assertMissing("villas/{$villa->id}/images/retry/main.webp");
    Storage::disk('public')->assertMissing("villas/{$villa->id}/images/retry/thumb.webp");

    Queue::assertPushed(ProcessVillaImage::class, fn (ProcessVillaImage $job) => $job->villaImageId === $image->id);
});

test('selected villa gallery images can be deleted in bulk', function () {
    $villa = Villa::query()->create([
        'name' => 'Villa Bulk Delete Test',
        'slug' => 'villa-bulk-delete-test',
        'location' => 'Puncak',
        'is_resort' => false,
        'status' => 'active',
    ]);

    Storage::disk('public')->put("villas/{$villa->id}/images/one/original.jpg", 'one');
    Storage::disk('public')->put("villas/{$villa->id}/images/two/original.jpg", 'two');
    Storage::disk('public')->put("villas/{$villa->id}/images/three/original.jpg", 'three');

    $first = VillaImage::query()->create([
        'villa_id' => $villa->id,
        'uuid' => (string) Str::uuid(),
        'disk' => 'public',
        'original_path' => "villas/{$villa->id}/images/one/original.jpg",
        'original_name' => 'one.jpg',
        'status' => 'ready',
        'sort_order' => 1,
        'is_cover' => true,
    ]);

    $second = VillaImage::query()->create([
        'villa_id' => $villa->id,
        'uuid' => (string) Str::uuid(),
        'disk' => 'public',
        'original_path' => "villas/{$villa->id}/images/two/original.jpg",
        'original_name' => 'two.jpg',
        'status' => 'ready',
        'sort_order' => 2,
        'is_cover' => false,
    ]);

    $third = VillaImage::query()->create([
        'villa_id' => $villa->id,
        'uuid' => (string) Str::uuid(),
        'disk' => 'public',
        'original_path' => "villas/{$villa->id}/images/three/original.jpg",
        'original_name' => 'three.jpg',
        'status' => 'ready',
        'sort_order' => 3,
        'is_cover' => false,
    ]);

    delete(route('villas.images.bulk-destroy', $villa), [
        'selected_image_ids' => [$first->id, $second->id],
    ])->assertRedirect();

    expect($villa->images()->count())->toBe(1)
        ->and($villa->images()->first()?->id)->toBe($third->id)
        ->and($villa->images()->first()?->is_cover)->toBeTrue()
        ->and($villa->images()->first()?->sort_order)->toBe(1);

    Storage::disk('public')->assertMissing("villas/{$villa->id}/images/one/original.jpg");
    Storage::disk('public')->assertMissing("villas/{$villa->id}/images/two/original.jpg");
    Storage::disk('public')->assertExists("villas/{$villa->id}/images/three/original.jpg");
});
