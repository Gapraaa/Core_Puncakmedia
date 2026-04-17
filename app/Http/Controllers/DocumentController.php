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

        $isDownload = request()->boolean('download');

        $this->auditLog(
            module: 'dokumen',
            action: $isDownload ? 'download_invoice' : 'view_invoice',
            description: $isDownload
                ? 'Invoice berhasil diunduh.'
                : 'Invoice dibuka untuk dilihat.',
            subject: $invoice,
            properties: [
                'booking_code' => $invoice->booking?->booking_code,
                'guest_name' => $invoice->booking?->guest_name,
                'download' => $isDownload ? 'ya' : 'tidak',
            ],
        );

        $pdf = Pdf::loadView('pages.documents.invoice', [
            'title' => 'Invoice ' . $invoice->invoice_number,
            'invoice' => $invoice,
            'autoPrint' => false,
        ])->setPaper('a4');

        $filename = Str::upper('invoice-' . $invoice->invoice_number . '.pdf');

        return $isDownload
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

        $isDownload = request()->boolean('download');

        $this->auditLog(
            module: 'dokumen',
            action: $isDownload ? 'download_receipt' : 'view_receipt',
            description: $isDownload
                ? 'Bukti pembayaran berhasil diunduh.'
                : 'Bukti pembayaran dibuka untuk dilihat.',
            subject: $payment,
            properties: [
                'booking_code' => $payment->booking?->booking_code,
                'invoice_number' => $payment->invoice?->invoice_number,
                'amount' => $payment->amount,
                'download' => $isDownload ? 'ya' : 'tidak',
            ],
        );

        $pdf = Pdf::loadView('pages.documents.receipt', [
            'title' => 'Bukti Pembayaran ' . $payment->id,
            'payment' => $payment,
            'autoPrint' => false,
        ])->setPaper('a4');

        $filename = Str::upper('bukti-pembayaran-' . ($payment->invoice?->invoice_number ?? $payment->id) . '.pdf');

        return $isDownload
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
