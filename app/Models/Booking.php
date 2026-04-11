<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'booking_code',
        'guest_name',
        'guest_phone',
        'brand_id',
        'villa_id',
        'villa_unit_id',
        'check_in',
        'check_out',
        'total_before_discount',
        'voucher_id',
        'voucher_discount_amount',
        'manual_discount_amount',
        'manual_discount_reason',
        'grand_total',
        'total_paid',
        'remaining_balance',
        'payment_status',
        'booking_status',
        'guest_link_token',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function villaUnit(): BelongsTo
    {
        return $this->belongsTo(VillaUnit::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
