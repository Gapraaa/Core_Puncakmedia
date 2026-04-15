<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function showInvoice(Invoice $invoice): Response
    {
        $invoice->load([
            'booking.brand',
            'booking.villa',
            'booking.villaUnit',
            'booking.voucher',
            'items',
            'payments',
        ]);

        $pdf = Pdf::loadView('pages.documents.invoice', [
            'title' => 'Invoice ' . $invoice->invoice_number,
            'invoice' => $invoice,
            'autoPrint' => false,
        ])->setPaper('a4');

        $filename = Str::upper('invoice-' . $invoice->invoice_number . '.pdf');

        return request()->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    public function showReceipt(Payment $payment): Response
    {
        $payment->load([
            'invoice.booking.brand',
            'invoice.booking.villa',
            'invoice.booking.villaUnit',
        ]);

        $pdf = Pdf::loadView('pages.documents.receipt', [
            'title' => 'Bukti Pembayaran ' . $payment->id,
            'payment' => $payment,
            'autoPrint' => false,
        ])->setPaper('a4');

        $filename = Str::upper('bukti-pembayaran-' . ($payment->invoice?->invoice_number ?? $payment->id) . '.pdf');

        return request()->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
