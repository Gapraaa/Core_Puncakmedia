<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f3f4f6;
            color: #111827;
            text-transform: uppercase;
        }

        .page {
            max-width: 840px;
            margin: 24px auto;
            background: #ffffff;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .header, .grid {
            display: grid;
            gap: 16px;
        }

        .header {
            grid-template-columns: 1.1fr 1fr;
            align-items: start;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 24px;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
            background: #fcfcfd;
        }

        h1, h2, p {
            margin: 0;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        h2 {
            font-size: 15px;
            color: #374151;
            margin-bottom: 10px;
        }

        .muted {
            color: #6b7280;
            font-size: 12px;
        }

        .normal-case {
            text-transform: none;
        }

        .strong {
            font-weight: 700;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .row:last-child {
            margin-bottom: 0;
        }

        .amount {
            margin-top: 24px;
            padding: 24px;
            border-radius: 18px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }

        .amount .label {
            font-size: 13px;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .amount .value {
            font-size: 34px;
            font-weight: 700;
            margin-top: 10px;
            color: #1e3a8a;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page {
                margin: 0;
                max-width: none;
                box-shadow: none;
                padding: 18px;
            }
        }
    </style>
</head>
<body @if($autoPrint) onload="window.print()" @endif>
    <div class="page">
        <div class="header">
            <div>
                <h1>Bukti Pembayaran</h1>
                <p class="muted">Dokumen tanda terima pembayaran booking</p>
            </div>
            <div class="card">
                <div class="row"><span>No. Invoice</span><span class="strong">{{ strtoupper($payment->invoice?->invoice_number ?? '-') }}</span></div>
                <div class="row"><span>Tanggal Bayar</span><span>{{ $payment->paid_at?->format('d M Y H:i') }}</span></div>
                <div class="row"><span>Metode</span><span>{{ ucfirst($payment->payment_method) }}</span></div>
                <div class="row"><span>Diterima Oleh</span><span>{{ ucfirst(str_replace('_', ' ', $payment->received_by)) }}</span></div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h2>Data Tamu</h2>
                <p class="strong">{{ strtoupper($payment->booking?->guest_name ?? '-') }}</p>
                <p class="muted normal-case" style="margin-top: 6px;">{{ $payment->booking?->guest_phone }}</p>
            </div>
            <div class="card">
                <h2>Data Booking</h2>
                <p class="strong">{{ strtoupper($payment->booking?->booking_code ?? '-') }}</p>
                <p class="muted">{{ strtoupper($payment->booking?->villa?->name ?? '-') }}</p>
                @if ($payment->booking?->villa?->is_resort)
                    <p class="muted">UNIT: {{ strtoupper($payment->booking?->villaUnit?->unit_name ?? '-') }}</p>
                @endif
                <p class="muted">{{ strtoupper($payment->invoice?->label ?? 'INVOICE UTAMA') }}</p>
            </div>
        </div>

        <div class="amount">
            <div class="label">Nominal Pembayaran Diterima</div>
            <div class="value">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
        </div>

        <div class="grid">
            <div class="card">
                <h2>Catatan</h2>
                <p>{{ strtoupper($payment->note ?: '-') }}</p>
            </div>
            <div class="card">
                <h2>Ringkasan Invoice</h2>
                <div class="row"><span>Subtotal</span><span>Rp {{ number_format($payment->invoice?->subtotal ?? 0, 0, ',', '.') }}</span></div>
                <div class="row"><span>Total Dibayar</span><span>Rp {{ number_format($payment->invoice?->total_paid ?? 0, 0, ',', '.') }}</span></div>
                <div class="row"><span>Sisa Invoice</span><span>Rp {{ number_format($payment->invoice?->remaining_balance ?? 0, 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>
</body>
</html>
