<?php

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Brand;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Models\Villa;
use App\Models\VillaUnit;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('successful login creates audit log', function () {
    Role::query()->firstOrCreate(['slug' => 'master'], ['name' => 'Master']);

    User::query()->updateOrCreate([
        'username' => 'audit-master',
    ], [
        'name' => 'Audit Master',
        'email' => 'audit-master@example.com',
        'password' => 'password',
        'is_active' => true,
    ]);

    post(route('signin.store'), [
        'login' => 'audit-master',
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    expect(AuditLog::query()->where('module', 'auth')->where('action', 'login')->exists())->toBeTrue();
});

test('master can open audit log page', function () {
    $masterRole = Role::query()->firstOrCreate(['slug' => 'master'], ['name' => 'Master']);
    $user = User::factory()->create();
    $user->roles()->attach($masterRole);

    actingAs($user);

    get(route('audit-logs.index'))
        ->assertOk()
        ->assertSee('Audit Log', false);
});

test('profile update is recorded in audit log', function () {
    $role = Role::query()->firstOrCreate(['slug' => 'admin-sales'], ['name' => 'Admin Sales']);
    $user = User::factory()->create([
        'name' => 'Lama',
        'username' => 'lama',
        'email' => 'lama@example.com',
    ]);
    $user->roles()->attach($role);

    actingAs($user);

    put(route('profile.update'), [
        'name' => 'Baru',
        'username' => 'baru',
        'email' => 'baru@example.com',
    ])->assertRedirect(route('profile'));

    expect(
        AuditLog::query()
            ->where('module', 'profile')
            ->where('action', 'update')
            ->where('subject_id', $user->id)
            ->exists()
    )->toBeTrue();
});

test('invoice download is recorded in audit log', function () {
    $masterRole = Role::query()->firstOrCreate(['slug' => 'master'], ['name' => 'Master']);
    $user = User::factory()->create();
    $user->roles()->attach($masterRole);

    $brand = Brand::query()->create([
        'name' => 'Audit Brand',
        'slug' => 'audit-brand',
    ]);

    $villa = Villa::query()->create([
        'name' => 'Audit Villa',
        'slug' => 'audit-villa',
        'location' => 'Puncak',
        'is_resort' => false,
        'status' => 'published',
    ]);

    $unit = VillaUnit::query()->create([
        'villa_id' => $villa->id,
        'unit_name' => 'Unit Audit',
        'unit_type' => 'private',
        'capacity' => 4,
        'price_weekday' => 1000000,
        'price_semi_weekend' => 1200000,
        'price_weekend' => 1500000,
        'status' => 'active',
    ]);

    $booking = Booking::query()->create([
        'invoice_no' => 'INV-AUDIT-001',
        'booking_code' => 'BOOK-AUDIT-001',
        'guest_name' => 'Tamu Audit',
        'guest_phone' => '08123456789',
        'brand_id' => $brand->id,
        'villa_id' => $villa->id,
        'villa_unit_id' => $unit->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'total_before_discount' => 1000000,
        'voucher_discount_amount' => 0,
        'manual_discount_amount' => 0,
        'grand_total' => 1000000,
        'total_paid' => 500000,
        'remaining_balance' => 500000,
        'payment_status' => 'dp',
        'booking_status' => 'confirmed',
        'guest_link_token' => 'audit-token',
        'created_by' => $user->id,
    ]);

    $invoice = Invoice::query()->create([
        'booking_id' => $booking->id,
        'invoice_number' => 'INV-AUDIT-001',
        'label' => 'INVOICE UTAMA',
        'invoice_type' => 'combined',
        'subtotal' => 1000000,
        'total_paid' => 500000,
        'remaining_balance' => 500000,
        'payment_status' => 'dp',
    ]);

    Payment::query()->create([
        'booking_id' => $booking->id,
        'invoice_id' => $invoice->id,
        'amount' => 500000,
        'payment_method' => 'transfer',
        'received_by' => 'Admin',
        'note' => 'DP',
        'paid_at' => now(),
        'created_by' => $user->id,
    ]);

    actingAs($user);

    get(route('documents.invoices.show', ['invoice' => $invoice, 'download' => 1]))
        ->assertOk();

    expect(
        AuditLog::query()
            ->where('module', 'dokumen')
            ->where('action', 'download_invoice')
            ->where('subject_id', $invoice->id)
            ->exists()
    )->toBeTrue();
});

test('master can export audit log csv', function () {
    $masterRole = Role::query()->firstOrCreate(['slug' => 'master'], ['name' => 'Master']);
    $user = User::factory()->create();
    $user->roles()->attach($masterRole);

    AuditLog::query()->create([
        'user_id' => $user->id,
        'module' => 'users',
        'action' => 'create',
        'description' => 'User internal baru dibuat.',
        'subject_type' => User::class,
        'subject_id' => $user->id,
        'subject_label' => $user->name,
        'created_at' => now(),
    ]);

    actingAs($user);

    get(route('audit-logs.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
