<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Brand;
use App\Models\Payment;
use App\Models\Villa;
use App\Models\VillaUnit;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $paymentStatusCounts = Booking::query()
            ->selectRaw('payment_status, COUNT(*) as aggregate')
            ->groupBy('payment_status')
            ->pluck('aggregate', 'payment_status');

        return view('pages.dashboard.core-pms', [
            'title' => 'Dasbor Core PMS',
            'brandCount' => Brand::query()->count(),
            'villaCount' => Villa::query()->count(),
            'villaUnitCount' => VillaUnit::query()->count(),
            'bookingCount' => Booking::query()->count(),
            'upcomingCheckInsCount' => Booking::query()->whereDate('check_in', '>=', $today)->count(),
            'totalOutstanding' => (int) Booking::query()->sum('remaining_balance'),
            'paymentStatusCounts' => [
                'dp' => (int) ($paymentStatusCounts['dp'] ?? 0),
                'cicil' => (int) ($paymentStatusCounts['cicil'] ?? 0),
                'lunas' => (int) ($paymentStatusCounts['lunas'] ?? 0),
            ],
            'upcomingCheckIns' => Booking::query()
                ->with(['brand', 'villa', 'villaUnit'])
                ->whereDate('check_in', '>=', $today)
                ->orderBy('check_in')
                ->limit(5)
                ->get(),
            'recentPayments' => Payment::query()
                ->with('booking')
                ->latest('paid_at')
                ->limit(5)
                ->get(),
            'recentBookings' => Booking::query()
                ->with(['brand', 'villa', 'villaUnit'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
