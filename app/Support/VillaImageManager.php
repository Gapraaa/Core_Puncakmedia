<?php

namespace App\Support;

use App\Models\Villa;
use App\Models\VillaImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class VillaImageManager
{
    public function uploadOriginal(Villa $villa, UploadedFile $file): VillaImage
    {
        $disk = config('filesystems.villa_media_disk', 'public');
        $uuid = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $baseDirectory = $this->baseDirectory($villa, $uuid);
        $originalPath = $baseDirectory.'/original.'.$extension;

        Storage::disk($disk)->put($originalPath, file_get_contents($file->getRealPath()), [
            'visibility' => 'public',
        ]);

        $imageSize = @getimagesize($file->getRealPath()) ?: [null, null, null, null, 'mime' => $file->getMimeType()];

        return $villa->images()->create([
            'uuid' => $uuid,
            'disk' => $disk,
            'original_path' => $originalPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $imageSize['mime'] ?? $file->getMimeType(),
            'file_size' => $file->getSize(),
            'width' => $imageSize[0] ?? null,
            'height' => $imageSize[1] ?? null,
            'sort_order' => (int) $villa->images()->max('sort_order') + 1,
            'is_cover' => ! $villa->images()->where('is_cover', true)->exists(),
            'status' => 'pending',
        ]);
    }

    public function process(VillaImage $image): void
    {
        $disk = Storage::disk($image->disk);

        if (! $disk->exists($image->original_path)) {
            $image->update(['status' => 'failed']);

            return;
        }

        $binary = $disk->get($image->original_path);
        $resource = $this->createImageResource($binary, $image->mime_type);

        if (! $resource) {
            $image->update(['status' => 'failed']);

            return;
        }

        try {
            $baseDirectory = dirname($image->original_path);
            $mainPath = $baseDirectory.'/main.webp';
            $thumbPath = $baseDirectory.'/thumb.webp';

            $mainImage = $this->resizeToFit($resource, 1800, 1800);
            $thumbImage = $this->resizeToFit($resource, 720, 720);

            $disk->put($mainPath, $this->encodeWebp($mainImage, 84), ['visibility' => 'public']);
            $disk->put($thumbPath, $this->encodeWebp($thumbImage, 78), ['visibility' => 'public']);

            $image->forceFill([
                'webp_path' => $mainPath,
                'thumb_path' => $thumbPath,
                'status' => 'ready',
                'processed_at' => now(),
            ])->save();

            imagedestroy($mainImage);
            imagedestroy($thumbImage);
        } finally {
            imagedestroy($resource);
        }
    }

    public function deleteFiles(VillaImage $image): void
    {
        Storage::disk($image->disk)->deleteDirectory(dirname($image->original_path));
    }

    protected function baseDirectory(Villa $villa, string $uuid): string
    {
        return "villas/{$villa->id}/images/{$uuid}";
    }

    protected function createImageResource(string $binary, ?string $mimeType)
    {
        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromstring($binary),
            'image/png' => imagecreatefromstring($binary),
            'image/webp' => imagecreatefromstring($binary),
            'image/gif' => imagecreatefromstring($binary),
            default => null,
        };
    }

    protected function resizeToFit($source, int $maxWidth, int $maxHeight)
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            throw new RuntimeException('Ukuran gambar tidak valid.');
        }

        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight, 1);
        $targetWidth = max(1, (int) round($sourceWidth * $ratio));
        $targetHeight = max(1, (int) round($sourceHeight * $ratio));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        return $canvas;
    }

    protected function encodeWebp($resource, int $quality): string
    {
        ob_start();
        imagewebp($resource, null, $quality);

        return (string) ob_get_clean();
    }
}
