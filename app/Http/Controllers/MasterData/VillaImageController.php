<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVillaImage;
use App\Models\Villa;
use App\Models\VillaImage;
use App\Support\VillaImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VillaImageController extends Controller
{
    public function store(Request $request, Villa $villa, VillaImageManager $manager): RedirectResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'images.required' => 'Pilih minimal satu gambar untuk diunggah.',
            'images.*.mimes' => 'Format gambar hanya boleh JPG, JPEG, PNG, atau WEBP.',
            'images.*.max' => 'Ukuran setiap gambar maksimal 5 MB.',
        ]);

        $uploadedImages = DB::transaction(function () use ($validated, $villa, $manager) {
            $images = [];

            foreach ($validated['images'] as $file) {
                $images[] = $manager->uploadOriginal($villa, $file);
            }

            return $images;
        });

        foreach ($uploadedImages as $image) {
            ProcessVillaImage::dispatch($image->id)->afterCommit();
        }

        $this->auditLog(
            module: 'master-data',
            action: 'update',
            description: 'Gallery villa ditambahkan.',
            subject: $villa,
            properties: [
                'uploaded_images' => implode(', ', collect($uploadedImages)->pluck('original_name')->all()),
            ],
        );

        return back()->with('success', 'Gambar villa berhasil diunggah dan masuk antrean proses WebP.');
    }

    public function reorder(Request $request, Villa $villa): RedirectResponse
    {
        $validated = $request->validate([
            'ordered_image_ids' => ['required', 'array', 'min:1'],
            'ordered_image_ids.*' => [
                'required',
                'integer',
                Rule::exists('villa_images', 'id')->where(fn ($query) => $query->where('villa_id', $villa->id)),
            ],
        ]);

        DB::transaction(function () use ($validated, $villa): void {
            foreach (array_values($validated['ordered_image_ids']) as $index => $imageId) {
                $villa->images()->whereKey($imageId)->update([
                    'sort_order' => $index + 1,
                ]);
            }
        });

        $this->auditLog(
            module: 'master-data',
            action: 'update',
            description: 'Urutan gallery villa diperbarui.',
            subject: $villa,
        );

        return back()->with('success', 'Urutan gambar villa berhasil diperbarui.');
    }

    public function setCover(Villa $villa, VillaImage $villaImage): RedirectResponse
    {
        abort_unless($villaImage->villa_id === $villa->id, 404);

        DB::transaction(function () use ($villa, $villaImage): void {
            $villa->images()->update(['is_cover' => false]);
            $villaImage->update(['is_cover' => true]);
        });

        $this->auditLog(
            module: 'master-data',
            action: 'update',
            description: 'Cover villa diperbarui.',
            subject: $villa,
            properties: [
                'cover_image_id' => $villaImage->id,
                'cover_image_name' => $villaImage->original_name,
            ],
        );

        return back()->with('success', 'Cover villa berhasil diperbarui.');
    }

    public function destroy(Villa $villa, VillaImage $villaImage, VillaImageManager $manager): RedirectResponse
    {
        abort_unless($villaImage->villa_id === $villa->id, 404);

        DB::transaction(function () use ($villa, $villaImage, $manager): void {
            $wasCover = $villaImage->is_cover;
            $imageName = $villaImage->original_name;

            $manager->deleteFiles($villaImage);
            $villaImage->delete();

            $remainingImages = $villa->images()->orderBy('sort_order')->get();

            foreach ($remainingImages as $index => $image) {
                $image->update([
                    'sort_order' => $index + 1,
                    'is_cover' => $wasCover && $index === 0 ? true : $image->is_cover,
                ]);
            }

            if (! $remainingImages->where('is_cover', true)->count() && $remainingImages->isNotEmpty()) {
                $remainingImages->first()?->update(['is_cover' => true]);
            }

            $this->auditLog(
                module: 'master-data',
                action: 'delete',
                description: 'Gambar villa dihapus.',
                subject: $villa,
                properties: [
                    'deleted_image_name' => $imageName,
                ],
            );
        });

        return back()->with('success', 'Gambar villa berhasil dihapus.');
    }
}
