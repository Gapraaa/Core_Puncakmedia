<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'invoice_number',
        'label',
        'invoice_type',
        'subtotal',
        'total_paid',
        'remaining_balance',
        'payment_status',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function invoiceNumber(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Str::upper($value) : null,
        );
    }

    protected function label(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Str::upper($value) : null,
        );
    }

    protected function invoiceType(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Str::lower($value) : null,
        );
    }
}
