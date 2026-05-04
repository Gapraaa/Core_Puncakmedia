<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VillaImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'villa_id',
        'uuid',
        'disk',
        'original_path',
        'webp_path',
        'thumb_path',
        'original_name',
        'mime_type',
        'file_size',
        'width',
        'height',
        'sort_order',
        'is_cover',
        'status',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function getPreviewUrlAttribute(): ?string
    {
        return $this->buildUrl($this->thumb_path)
            ?? $this->buildUrl($this->webp_path)
            ?? $this->buildUrl($this->original_path);
    }

    public function getDisplayUrlAttribute(): ?string
    {
        return $this->buildUrl($this->webp_path)
            ?? $this->buildUrl($this->original_path);
    }

    public function getOriginalUrlAttribute(): ?string
    {
        return $this->buildUrl($this->original_path);
    }

    protected function buildUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk($this->disk)->url($path);
    }
}
