<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Villa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'capacity',
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
}
