<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherRequest;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $vouchers = Voucher::query()
            ->withCount('bookings')
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where('code', 'like', "%{$keyword}%");
            })
            ->when($request->filled('discount_type'), fn (Builder $query): Builder => $query->where('discount_type', $request->string('discount_type')))
            ->when($request->filled('is_active'), function (Builder $query) use ($request): Builder {
                return $query->where('is_active', $request->string('is_active') === '1');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.vouchers.index', [
            'title' => 'Voucher',
            'vouchers' => $vouchers,
            'filters' => $request->only(['q', 'discount_type', 'is_active']),
        ]);
    }

    public function create(): View
    {
        return view('pages.vouchers.create', [
            'title' => 'Buat Voucher',
            'voucher' => new Voucher(['discount_type' => 'fixed', 'is_active' => true, 'minimum_transaction' => 0]),
        ]);
    }

    public function show(Voucher $voucher): View
    {
        return view('pages.vouchers.show', [
            'title' => 'Detail Voucher',
            'voucher' => $voucher->load(['bookings' => fn ($query) => $query->latest()->limit(5)]),
        ]);
    }

    public function store(VoucherRequest $request): RedirectResponse
    {
        $voucher = Voucher::query()->create($this->validatedData($request));

        $this->auditLog(
            module: 'master-data',
            action: 'create',
            description: 'Voucher baru berhasil dibuat.',
            subject: $voucher,
            after: $voucher->only(['code', 'discount_type', 'amount', 'minimum_transaction', 'is_active']),
        );

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('pages.vouchers.edit', [
            'title' => 'Edit Voucher',
            'voucher' => $voucher,
        ]);
    }

    public function update(VoucherRequest $request, Voucher $voucher): RedirectResponse
    {
        $before = $voucher->only(['code', 'discount_type', 'amount', 'minimum_transaction', 'is_active']);
        $voucher->update($this->validatedData($request));

        $this->auditLog(
            module: 'master-data',
            action: 'update',
            description: 'Voucher berhasil diperbarui.',
            subject: $voucher,
            before: $before,
            after: $voucher->fresh()->only(['code', 'discount_type', 'amount', 'minimum_transaction', 'is_active']),
        );

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $before = $voucher->only(['code', 'discount_type', 'amount', 'minimum_transaction', 'is_active']);
        $voucher->delete();

        $this->auditLog(
            module: 'master-data',
            action: 'delete',
            description: 'Voucher dihapus dari sistem.',
            subject: $voucher,
            before: $before,
        );

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dihapus.');
    }

    protected function validatedData(VoucherRequest $request): array
    {
        $data = $request->validated();
        $data['code'] = Str::upper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
