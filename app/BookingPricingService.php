<?php

namespace App;

use App\Models\SeasonalPrice;
use App\Models\VillaUnit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class BookingPricingService
{
    public function buildNightItems(VillaUnit $villaUnit, Carbon $checkIn, Carbon $checkOut): Collection
    {
        $lastNight = $checkOut->copy()->subDay();

        if ($checkIn->greaterThan($lastNight)) {
            return collect();
        }

        return collect(CarbonPeriod::create($checkIn, $lastNight))
            ->map(function (Carbon $night) use ($villaUnit): array {
                $price = $this->resolveNightPrice($villaUnit, $night);

                return [
                    'item_type' => 'night',
                    'item_name' => sprintf('Harga per malam %s', $night->format('Y-m-d')),
                    'reference_date' => $night->toDateString(),
                    'quantity' => 1,
                    'unit_price' => $price,
                    'total_price' => $price,
                    'notes' => $this->resolvePriceSourceNote($villaUnit, $night),
                ];
            })
            ->values();
    }

    public function resolveNightPrice(VillaUnit $villaUnit, Carbon $night): int
    {
        $seasonalPrice = $this->findSeasonalPrice($villaUnit, $night);

        if ($seasonalPrice !== null) {
            return (int) $seasonalPrice->price;
        }

        return match ((int) $night->dayOfWeek) {
            Carbon::FRIDAY => (int) $villaUnit->price_semi_weekend,
            Carbon::SATURDAY => (int) $villaUnit->price_weekend,
            default => (int) $villaUnit->price_weekday,
        };
    }

    public function resolvePriceSourceNote(VillaUnit $villaUnit, Carbon $night): string
    {
        $seasonalPrice = $this->findSeasonalPrice($villaUnit, $night);

        if ($seasonalPrice !== null) {
            return $seasonalPrice->note ?: 'Harga high season';
        }

        return match ((int) $night->dayOfWeek) {
            Carbon::FRIDAY => 'Harga semi weekend',
            Carbon::SATURDAY => 'Harga weekend',
            default => 'Harga weekday',
        };
    }

    protected function findSeasonalPrice(VillaUnit $villaUnit, Carbon $night): ?SeasonalPrice
    {
        $loadedPrices = $villaUnit->relationLoaded('seasonalPrices')
            ? $villaUnit->seasonalPrices
            : $villaUnit->seasonalPrices()->get();

        return $loadedPrices
            ->first(function (SeasonalPrice $seasonalPrice) use ($night): bool {
                return $night->betweenIncluded(
                    Carbon::parse($seasonalPrice->start_date),
                    Carbon::parse($seasonalPrice->end_date),
                );
            });
    }
}
