<?php

namespace App\Jobs;

use App\Models\VillaImage;
use App\Support\VillaImageManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessVillaImage implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $villaImageId)
    {
    }

    public function handle(VillaImageManager $manager): void
    {
        $image = VillaImage::query()->find($this->villaImageId);

        if (! $image) {
            return;
        }

        $image->update(['status' => 'processing']);

        try {
            $manager->process($image->fresh());
        } catch (\Throwable $exception) {
            $image->fresh()?->update(['status' => 'failed']);

            throw $exception;
        }
    }
}
