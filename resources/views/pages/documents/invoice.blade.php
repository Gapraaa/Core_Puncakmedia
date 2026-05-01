<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 0;
            background: #f3f4f6;
            text-transform: uppercase;
        }

        .page {
            max-width: 920px;
            margin: 24px auto;
            background: #ffffff;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .header, .meta-grid, .summary-grid, .totals {
            display: grid;
            gap: 16px;
        }

        .header {
            grid-template-columns: 1.3fr 1fr;
            align-items: start;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }

        .meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 24px;
        }

        .summary-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-top: 24px;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
            background: #fcfcfd;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        h2 {
            font-size: 15px;
            margin-bottom: 10px;
            color: #374151;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }

        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
            font-size: 13px;
        }

        th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
        }

        .right {
            text-align: right;
        }

        .totals {
            margin-top: 24px;
            justify-items: end;
        }

        .totals-box {
            width: 100%;
            max-width: 360px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 18px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .totals-row:last-child {
            margin-bottom: 0;
        }

        .totals-row.grand {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px dashed #d1d5db;
            font-size: 16px;
            font-weight: 700;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
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
                <h1>Invoice</h1>
                <p class="muted">Dokumen tagihan booking villa</p>
            </div>
            <div class="card">
                <div class="totals-row"><span>No. Invoice</span><span class="strong">{{ strtoupper($invoice->invoice_number) }}</span></div>
                <div class="totals-row"><span>Label</span><span>{{ strtoupper($invoice->label) }}</span></div>
                <div class="totals-row"><span>Status</span><span class="status">{{ strtoupper($invoice->payment_status) }}</span></div>
                <div class="totals-row"><span>Kode Booking</span><span>{{ strtoupper($invoice->booking?->booking_code) }}</span></div>
            </div>
        </div>

        <div class="meta-grid">
            <div class="card">
                <h2>Data Tamu</h2>
                <p class="strong">{{ strtoupper($invoice->booking?->guest_name ?? '-') }}</p>
                <p class="muted normal-case" style="margin-top: 6px;">{{ $invoice->booking?->guest_phone }}</p>
            </div>
            <div class="card">
                <h2>Data Villa</h2>
                <p class="strong">{{ strtoupper($invoice->booking?->villa?->name ?? '-') }}</p>
                @if ($invoice->booking?->villa?->is_resort)
                    <p class="muted">UNIT: {{ strtoupper($invoice->booking?->villaUnit?->unit_name ?? '-') }}</p>
                @endif
                <p class="muted" style="margin-top: 6px;">{{ $invoice->booking?->check_in?->format('d M Y') }} - {{ $invoice->booking?->check_out?->format('d M Y') }}</p>
            </div>
        </div>

        <div class="summary-grid">
            <div class="card">
                <h2>Brand</h2>
                <p class="strong">{{ strtoupper($invoice->booking?->brand?->name ?? '-') }}</p>
            </div>
            <div class="card">
                <h2>Total Dibayar</h2>
                <p class="strong">Rp {{ number_format($invoice->total_paid, 0, ',', '.') }}</p>
            </div>
            <div class="card">
                <h2>Sisa Tagihan</h2>
                <p class="strong">Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</p>
            </div>
            <div class="card">
                <h2>Pelunasan</h2>
                <p class="strong">
                    @if ($invoice->booking?->final_payment_due_date?->isSameDay($invoice->booking?->check_in))
                        SAAT CHECK-IN
                    @else
                        {{ strtoupper($invoice->booking?->final_payment_due_date?->format('d M Y') ?? '-') }}
                    @endif
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Deskripsi</th>
                    <th>Tanggal</th>
                    <th class="right">Qty</th>
                    <th class="right">Harga</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->item_type }}</td>
                        <td>
                            <div class="strong">{{ strtoupper($item->item_name) }}</div>
                            @if ($item->notes)
                                <div class="muted">{{ strtoupper($item->notes) }}</div>
                            @endif
                        </td>
                        <td>{{ $item->reference_date?->format('d M Y') ?? '-' }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-box">
                <div class="totals-row"><span>Subtotal</span><span>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span></div>
                <div class="totals-row"><span>Total Dibayar</span><span>Rp {{ number_format($invoice->total_paid, 0, ',', '.') }}</span></div>
                <div class="totals-row grand"><span>Sisa Tagihan</span><span>Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>
</body>
</html>
