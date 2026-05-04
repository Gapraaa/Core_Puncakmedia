<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Villa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'is_resort',
        'status',
        'rules',
        'pros',
        'cons',
        'youtube_url',
    ];

    protected function casts(): array
    {
        return [
            'is_resort' => 'boolean',
        ];
    }

    public function units(): HasMany
    {
        return $this->hasMany(VillaUnit::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'villa_brand')->withTimestamps();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(VillaFacility::class)->orderBy('type')->orderBy('sort_order');
    }

    public function primaryFacilities(): HasMany
    {
        return $this->hasMany(VillaFacility::class)->where('type', 'primary')->orderBy('sort_order');
    }

    public function additionalFacilities(): HasMany
    {
        return $this->hasMany(VillaFacility::class)->where('type', 'additional')->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VillaImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(VillaImage::class)->where('is_cover', true);
    }
}
