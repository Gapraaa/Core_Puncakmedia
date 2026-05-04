<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillaFacility extends Model
{
    use HasFactory;

    protected $fillable = [
        'villa_id',
        'type',
        'name',
        'sort_order',
    ];

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }
}
