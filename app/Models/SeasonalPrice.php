<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonalPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'villa_unit_id',
        'start_date',
        'end_date',
        'price',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function villaUnit(): BelongsTo
    {
        return $this->belongsTo(VillaUnit::class);
    }
}
