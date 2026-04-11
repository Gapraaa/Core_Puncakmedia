<?php

namespace Database\Seeders;

use App\BookingPricingService;
use App\BookingTotalsService;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Brand;
use App\Models\Payment;
use App\Models\SeasonalPrice;
use App\Models\User;
use App\Models\Villa;
use App\Models\VillaUnit;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $pricingService = app(BookingPricingService::class);
        $totalsService = app(BookingTotalsService::class);

        $users = $this->seedUsers();
        $brands = $this->seedBrands();
        [$villas, $villaUnits] = $this->seedVillasAndUnits($brands);
        $this->seedSeasonalPrices($villaUnits);
        $addons = $this->seedAddons();
        $vouchers = $this->seedVouchers();

        $this->seedBookings(
            users: $users,
            brands: $brands,
            villas: $villas,
            villaUnits: $villaUnits,
            addons: $addons,
            vouchers: $vouchers,
            pricingService: $pricingService,
            totalsService: $totalsService,
        );
    }

    protected function seedUsers(): Collection
    {
        $users = collect([
            ['name' => 'Master User', 'username' => 'master', 'email' => 'master@puncakmedia.local'],
            ['name' => 'Superadmin User', 'username' => 'superadmin', 'email' => 'superadmin@puncakmedia.local'],
            ['name' => 'Head Office User', 'username' => 'headoffice', 'email' => 'headoffice@puncakmedia.local'],
            ['name' => 'Finance User', 'username' => 'finance', 'email' => 'finance@puncakmedia.local'],
            ['name' => 'Admin Sales User', 'username' => 'adminsales', 'email' => 'sales@puncakmedia.local'],
        ])->mapWithKeys(function (array $user): array {
            $record = User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'password' => 'password',
                ],
            );

            return [$user['username'] => $record];
        });

        return $users;
    }

    protected function seedBrands(): Collection
    {
        $brands = collect([
            [
                'name' => 'PuncakMediaBogor',
                'slug' => 'puncakmediabogor',
                'logo' => 'logos/puncakmediabogor.png',
                'bank_info' => 'BCA 1234567890 a.n. PuncakMediaBogor',
            ],
            [
                'name' => 'Ngevillayuk',
                'slug' => 'ngevillayuk',
                'logo' => 'logos/ngevillayuk.png',
                'bank_info' => 'Mandiri 9876543210 a.n. Ngevillayuk',
            ],
            [
                'name' => 'Kagivilla',
                'slug' => 'kagivilla',
                'logo' => 'logos/kagivilla.png',
                'bank_info' => 'BRI 4567891230 a.n. Kagivilla',
            ],
        ])->mapWithKeys(function (array $brand): array {
            $record = Brand::query()->updateOrCreate(
                ['slug' => $brand['slug']],
                $brand,
            );

            return [$brand['slug'] => $record];
        });

        return $brands;
    }

    protected function seedVillasAndUnits(Collection $brands): array
    {
        $villaDefinitions = [
            [
                'slug' => 'villa-alam-pinus',
                'name' => 'Villa Alam Pinus',
                'location' => 'Cisarua, Puncak',
                'capacity' => 20,
                'is_resort' => false,
                'status' => 'active',
                'description' => 'Villa keluarga besar dengan halaman luas dan view gunung.',
                'rules' => 'Tidak menerima pesta besar setelah jam 10 malam.',
                'pros' => 'Halaman luas, parkir lega, cocok keluarga besar.',
                'cons' => 'Akses jalan menanjak untuk bus besar.',
                'youtube_url' => 'https://youtube.com/watch?v=alam-pinus',
                'brand_slugs' => ['puncakmediabogor', 'ngevillayuk'],
                'units' => [
                    ['unit_name' => 'Pinus A', 'unit_type' => 'private', 'capacity' => 10, 'price_weekday' => 900000, 'price_semi_weekend' => 1100000, 'price_weekend' => 1300000],
                    ['unit_name' => 'Pinus B', 'unit_type' => 'private', 'capacity' => 12, 'price_weekday' => 1000000, 'price_semi_weekend' => 1200000, 'price_weekend' => 1400000],
                ],
            ],
            [
                'slug' => 'villa-pondok-awan',
                'name' => 'Villa Pondok Awan',
                'location' => 'Megamendung, Puncak',
                'capacity' => 18,
                'is_resort' => false,
                'status' => 'active',
                'description' => 'Villa sejuk dengan kolam renang dan area BBQ.',
                'rules' => 'Maksimal tambahan tamu 4 orang dengan biaya extra person.',
                'pros' => 'Kolam renang private dan area BBQ.',
                'cons' => 'Sinyal operator tertentu tidak terlalu stabil.',
                'youtube_url' => 'https://youtube.com/watch?v=pondok-awan',
                'brand_slugs' => ['puncakmediabogor'],
                'units' => [
                    ['unit_name' => 'Awan 1', 'unit_type' => 'private', 'capacity' => 8, 'price_weekday' => 850000, 'price_semi_weekend' => 1050000, 'price_weekend' => 1250000],
                    ['unit_name' => 'Awan 2', 'unit_type' => 'private', 'capacity' => 10, 'price_weekday' => 950000, 'price_semi_weekend' => 1150000, 'price_weekend' => 1350000],
                ],
            ],
            [
                'slug' => 'villa-kebun-teh',
                'name' => 'Villa Kebun Teh',
                'location' => 'Ciloto, Puncak',
                'capacity' => 16,
                'is_resort' => false,
                'status' => 'active',
                'description' => 'Villa dengan panorama kebun teh dan balkon luas.',
                'rules' => 'Tidak memperbolehkan hewan peliharaan.',
                'pros' => 'Pemandangan kebun teh dan udara dingin.',
                'cons' => 'Jalan masuk agak sempit untuk mobil besar.',
                'youtube_url' => 'https://youtube.com/watch?v=kebun-teh',
                'brand_slugs' => ['ngevillayuk', 'kagivilla'],
                'units' => [
                    ['unit_name' => 'Teh Hijau', 'unit_type' => 'suite', 'capacity' => 6, 'price_weekday' => 700000, 'price_semi_weekend' => 900000, 'price_weekend' => 1100000],
                    ['unit_name' => 'Teh Melati', 'unit_type' => 'suite', 'capacity' => 8, 'price_weekday' => 800000, 'price_semi_weekend' => 1000000, 'price_weekend' => 1200000],
                ],
            ],
            [
                'slug' => 'villa-cemara-hill',
                'name' => 'Villa Cemara Hill',
                'location' => 'Gadog, Bogor',
                'capacity' => 22,
                'is_resort' => true,
                'status' => 'active',
                'description' => 'Kompleks villa dengan beberapa unit dan fasilitas outbond.',
                'rules' => 'Check-in minimal H-1 untuk grup besar.',
                'pros' => 'Cocok gathering dan outing kantor.',
                'cons' => 'Perlu koordinasi lebih awal untuk catering.',
                'youtube_url' => 'https://youtube.com/watch?v=cemara-hill',
                'brand_slugs' => ['kagivilla'],
                'units' => [
                    ['unit_name' => 'Cemara Executive', 'unit_type' => 'executive', 'capacity' => 12, 'price_weekday' => 1250000, 'price_semi_weekend' => 1450000, 'price_weekend' => 1650000],
                    ['unit_name' => 'Cemara Family', 'unit_type' => 'family', 'capacity' => 10, 'price_weekday' => 1100000, 'price_semi_weekend' => 1300000, 'price_weekend' => 1500000],
                ],
            ],
            [
                'slug' => 'villa-lakeview-asri',
                'name' => 'Villa Lakeview Asri',
                'location' => 'Sukamakmur, Bogor',
                'capacity' => 14,
                'is_resort' => false,
                'status' => 'active',
                'description' => 'Villa tenang dekat danau dengan nuansa kayu hangat.',
                'rules' => 'Dilarang membawa sound system luar tanpa izin.',
                'pros' => 'View danau dan area santai luas.',
                'cons' => 'Area parkir terbatas untuk lebih dari 5 mobil.',
                'youtube_url' => 'https://youtube.com/watch?v=lakeview-asri',
                'brand_slugs' => ['puncakmediabogor', 'kagivilla'],
                'units' => [
                    ['unit_name' => 'Lakeview Utama', 'unit_type' => 'private', 'capacity' => 14, 'price_weekday' => 1050000, 'price_semi_weekend' => 1250000, 'price_weekend' => 1450000],
                ],
            ],
            [
                'slug' => 'villa-gardenia-resort',
                'name' => 'Villa Gardenia Resort',
                'location' => 'Ciawi, Bogor',
                'capacity' => 24,
                'is_resort' => true,
                'status' => 'active',
                'description' => 'Resort mini dengan beberapa unit dan taman acara.',
                'rules' => 'Deposit kebersihan wajib untuk event gathering.',
                'pros' => 'Area acara luas dan cocok untuk komunitas.',
                'cons' => 'Jadwal padat pada high season.',
                'youtube_url' => 'https://youtube.com/watch?v=gardenia-resort',
                'brand_slugs' => ['ngevillayuk'],
                'units' => [
                    ['unit_name' => 'Gardenia Deluxe', 'unit_type' => 'deluxe', 'capacity' => 10, 'price_weekday' => 1200000, 'price_semi_weekend' => 1400000, 'price_weekend' => 1600000],
                    ['unit_name' => 'Gardenia Suite', 'unit_type' => 'suite', 'capacity' => 14, 'price_weekday' => 1400000, 'price_semi_weekend' => 1600000, 'price_weekend' => 1800000],
                ],
            ],
        ];

        $villas = collect();
        $villaUnits = collect();

        foreach ($villaDefinitions as $definition) {
            $villa = Villa::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'slug' => $definition['slug'],
                    'location' => $definition['location'],
                    'description' => $definition['description'],
                    'capacity' => $definition['capacity'],
                    'is_resort' => $definition['is_resort'],
                    'status' => $definition['status'],
                    'rules' => $definition['rules'],
                    'pros' => $definition['pros'],
                    'cons' => $definition['cons'],
                    'youtube_url' => $definition['youtube_url'],
                ],
            );

            $brandIds = collect($definition['brand_slugs'])
                ->map(fn (string $slug) => $brands[$slug]->id)
                ->all();

            $villa->brands()->sync($brandIds);
            $villas->put($definition['slug'], $villa);

            foreach ($definition['units'] as $unit) {
                $unitSlug = Str::slug($definition['slug'] . '-' . $unit['unit_name']);

                $villaUnit = VillaUnit::query()->updateOrCreate(
                    [
                        'villa_id' => $villa->id,
                        'unit_name' => $unit['unit_name'],
                    ],
                    [
                        'unit_type' => $unit['unit_type'],
                        'capacity' => $unit['capacity'],
                        'price_weekday' => $unit['price_weekday'],
                        'price_semi_weekend' => $unit['price_semi_weekend'],
                        'price_weekend' => $unit['price_weekend'],
                        'status' => 'active',
                    ],
                );

                $villaUnits->put($unitSlug, $villaUnit);
            }
        }

        return [$villas, $villaUnits];
    }

    protected function seedSeasonalPrices(Collection $villaUnits): void
    {
        $definitions = [
            ['unit_key' => 'villa-alam-pinus-pinus-a', 'start_date' => '2026-04-15', 'end_date' => '2026-04-20', 'price' => 1500000, 'note' => 'Libur panjang April'],
            ['unit_key' => 'villa-alam-pinus-pinus-b', 'start_date' => '2026-05-10', 'end_date' => '2026-05-15', 'price' => 1650000, 'note' => 'High season Mei'],
            ['unit_key' => 'villa-kebun-teh-teh-hijau', 'start_date' => '2026-06-01', 'end_date' => '2026-06-07', 'price' => 1250000, 'note' => 'Pekan libur sekolah'],
            ['unit_key' => 'villa-cemara-hill-cemara-executive', 'start_date' => '2026-07-15', 'end_date' => '2026-07-22', 'price' => 1850000, 'note' => 'Gathering season'],
            ['unit_key' => 'villa-gardenia-resort-gardenia-suite', 'start_date' => '2026-12-20', 'end_date' => '2026-12-31', 'price' => 2200000, 'note' => 'Akhir tahun'],
        ];

        foreach ($definitions as $definition) {
            $villaUnit = $villaUnits[$definition['unit_key']] ?? null;

            if ($villaUnit === null) {
                continue;
            }

            SeasonalPrice::query()->updateOrCreate(
                [
                    'villa_unit_id' => $villaUnit->id,
                    'start_date' => $definition['start_date'],
                    'end_date' => $definition['end_date'],
                ],
                [
                    'price' => $definition['price'],
                    'note' => $definition['note'],
                ],
            );
        }
    }

    protected function seedAddons(): Collection
    {
        $definitions = [
            ['key' => 'extra-bed', 'name' => 'Extra Bed', 'price' => 150000, 'charge_type' => 'per_night', 'is_active' => true],
            ['key' => 'extra-person', 'name' => 'Extra Person', 'price' => 100000, 'charge_type' => 'per_night', 'is_active' => true],
            ['key' => 'bbq-package', 'name' => 'BBQ Package', 'price' => 350000, 'charge_type' => 'per_stay', 'is_active' => true],
            ['key' => 'floating-breakfast', 'name' => 'Floating Breakfast', 'price' => 250000, 'charge_type' => 'per_stay', 'is_active' => true],
            ['key' => 'decor-romantic', 'name' => 'Decor Romantic', 'price' => 450000, 'charge_type' => 'per_stay', 'is_active' => true],
            ['key' => 'karaoke-set', 'name' => 'Karaoke Set', 'price' => 200000, 'charge_type' => 'per_stay', 'is_active' => false],
        ];

        return collect($definitions)->mapWithKeys(function (array $definition): array {
            $record = Addon::query()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'price' => $definition['price'],
                    'charge_type' => $definition['charge_type'],
                    'is_active' => $definition['is_active'],
                ],
            );

            return [$definition['key'] => $record];
        });
    }

    protected function seedVouchers(): Collection
    {
        $definitions = [
            ['code' => 'HEMAT100', 'discount_type' => 'fixed', 'amount' => 100000, 'minimum_transaction' => 1000000, 'valid_until' => now()->addMonths(6)->toDateString(), 'is_active' => true],
            ['code' => 'WEEKDAY10', 'discount_type' => 'percentage', 'amount' => 10, 'minimum_transaction' => 800000, 'valid_until' => now()->addMonths(6)->toDateString(), 'is_active' => true],
            ['code' => 'BIGGROUP15', 'discount_type' => 'percentage', 'amount' => 15, 'minimum_transaction' => 2500000, 'valid_until' => now()->addMonths(3)->toDateString(), 'is_active' => true],
            ['code' => 'FLASH50', 'discount_type' => 'fixed', 'amount' => 50000, 'minimum_transaction' => 500000, 'valid_until' => now()->addMonth()->toDateString(), 'is_active' => true],
            ['code' => 'EXPIRED25', 'discount_type' => 'fixed', 'amount' => 250000, 'minimum_transaction' => 1000000, 'valid_until' => now()->subDays(5)->toDateString(), 'is_active' => false],
        ];

        return collect($definitions)->mapWithKeys(function (array $definition): array {
            $record = Voucher::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'discount_type' => $definition['discount_type'],
                    'amount' => $definition['amount'],
                    'minimum_transaction' => $definition['minimum_transaction'],
                    'valid_until' => $definition['valid_until'],
                    'is_active' => $definition['is_active'],
                ],
            );

            return [$definition['code'] => $record];
        });
    }

    protected function seedBookings(
        Collection $users,
        Collection $brands,
        Collection $villas,
        Collection $villaUnits,
        Collection $addons,
        Collection $vouchers,
        BookingPricingService $pricingService,
        BookingTotalsService $totalsService,
    ): void {
        $guestNames = [
            'Andi Wijaya', 'Budi Santoso', 'Citra Lestari', 'Dewi Anggraini', 'Eka Pratama', 'Farhan Hidayat',
            'Gina Maharani', 'Hendra Kurniawan', 'Indah Permata', 'Joko Saputra', 'Kartika Sari', 'Lukman Hakim',
            'Maya Puspita', 'Naufal Ramadhan', 'Oki Prabowo', 'Putri Anjani', 'Qori Rahma', 'Rizky Maulana',
            'Salsa Nabila', 'Teguh Saptono', 'Uli Febriana', 'Vina Kartika', 'Wawan Setiawan', 'Yuni Astuti',
        ];

        $phoneSeed = 81234567000;
        $unitPool = $villaUnits->values();
        $brandPool = $brands->values();
        $voucherPool = [$vouchers['HEMAT100'], $vouchers['WEEKDAY10'], $vouchers['BIGGROUP15'], $vouchers['FLASH50'], null];
        $addonSets = [
            [$addons['extra-bed']],
            [$addons['bbq-package']],
            [$addons['extra-person'], $addons['floating-breakfast']],
            [$addons['decor-romantic']],
            [],
        ];

        mt_srand(20260411);

        foreach (range(1, 24) as $index) {
            $bookingCode = sprintf('BOOK-DEMO-%03d', $index);
            $invoiceNo = sprintf('INV-DEMO-%03d', $index);
            $villaUnit = $unitPool[($index - 1) % $unitPool->count()];
            $villa = $villaUnit->villa()->firstOrFail();
            $brand = $brandPool[($index - 1) % $brandPool->count()];
            $voucher = $voucherPool[($index - 1) % count($voucherPool)];
            $selectedAddons = collect($addonSets[($index - 1) % count($addonSets)]);
            $createdBy = $index % 3 === 0 ? $users['finance'] : $users['adminsales'];

            $checkIn = Carbon::today()->subDays(20)->addDays($index * 2);
            $nights = 2 + ($index % 3);
            $checkOut = $checkIn->copy()->addDays($nights);
            $manualDiscount = $index % 4 === 0 ? 75000 : 0;
            $manualReason = $manualDiscount > 0 ? 'Diskon manual demo untuk kebutuhan operasional.' : null;

            $booking = Booking::query()->updateOrCreate(
                ['booking_code' => $bookingCode],
                [
                    'invoice_no' => $invoiceNo,
                    'guest_name' => $guestNames[$index - 1],
                    'guest_phone' => '08' . ($phoneSeed + $index),
                    'brand_id' => $brand->id,
                    'villa_id' => $villa->id,
                    'villa_unit_id' => $villaUnit->id,
                    'check_in' => $checkIn->toDateString(),
                    'check_out' => $checkOut->toDateString(),
                    'voucher_id' => $voucher?->id,
                    'manual_discount_amount' => $manualDiscount,
                    'manual_discount_reason' => $manualReason,
                    'guest_link_token' => Str::lower(Str::random(40)),
                    'created_by' => $createdBy->id,
                ],
            );

            $booking->items()->delete();
            $booking->payments()->delete();

            $villaUnit->load('seasonalPrices');
            $nightItems = $pricingService->buildNightItems($villaUnit, $checkIn, $checkOut);
            $addonItems = $selectedAddons->map(function (Addon $addon) use ($nights): array {
                $quantity = $addon->charge_type === 'per_night' ? $nights : 1;

                return [
                    'item_type' => 'addon',
                    'item_name' => $addon->name,
                    'reference_date' => null,
                    'quantity' => $quantity,
                    'unit_price' => (int) $addon->price,
                    'total_price' => (int) $addon->price * $quantity,
                    'notes' => $addon->charge_type === 'per_night' ? 'Add-on per malam' : 'Add-on per stay',
                ];
            });

            $items = $nightItems->concat($addonItems)->values();

            foreach ($items as $item) {
                $booking->items()->create($item);
            }

            $booking->refresh()->load(['items', 'payments', 'voucher']);
            $summary = $totalsService->summarize($booking, $booking->items, $booking->payments);
            $booking->update($summary);

            $paymentScenario = ($index - 1) % 4;

            if ($paymentScenario === 1) {
                $this->createPayment($booking, (int) round($booking->grand_total * 0.5), 'transfer', 'finance', $users['finance']->id, $checkIn->copy()->subDays(2));
            }

            if ($paymentScenario === 2) {
                $half = (int) round($booking->grand_total * 0.4);
                $this->createPayment($booking, $half, 'transfer', 'finance', $users['finance']->id, $checkIn->copy()->subDays(4));
                $this->createPayment($booking, $booking->grand_total - $half, 'cash', 'office', $users['finance']->id, $checkIn->copy()->subDays(1));
            }

            if ($paymentScenario === 3) {
                $first = (int) round($booking->grand_total * 0.35);
                $second = (int) round($booking->grand_total * 0.25);
                $this->createPayment($booking, $first, 'transfer', 'finance', $users['finance']->id, $checkIn->copy()->subDays(5));
                $this->createPayment($booking, $second, 'cash', 'office', $users['finance']->id, $checkIn->copy()->subDays(2));
            }

            $booking->refresh()->load(['items', 'payments', 'voucher']);
            $summary = $totalsService->summarize($booking, $booking->items, $booking->payments);
            $booking->update($summary);
        }
    }

    protected function createPayment(Booking $booking, int $amount, string $method, string $receiver, int $userId, Carbon $paidAt): void
    {
        if ($amount <= 0) {
            return;
        }

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'payment_method' => $method,
            'received_by' => $receiver,
            'note' => 'Payment demo untuk kebutuhan seed data.',
            'proof_image' => null,
            'paid_at' => $paidAt,
            'created_by' => $userId,
        ]);
    }
}
