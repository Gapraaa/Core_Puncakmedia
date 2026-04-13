# Payment Rules

## Currency Rule
- All payment values use Rupiah only.
- Store payment amounts as integer Rupiah values.
- Do not use decimal payment amounts and do not display trailing `.00` or `,00`.

## Core Principle
Payments are separate from booking totals.

## Payment Logic
remaining_balance = grand_total - sum(payments.amount)

## Payment Entry Flow
- DP (payment pertama) diinput langsung di form create booking (wajib).
- Cicilan / pelunasan diinput dari halaman detail booking.
- Tidak ada halaman "Catat Payment" standalone.
- Payment Ledger (daftar semua payment) tetap tersedia untuk Finance.

## Payment Status
- `dp` — baru ada 1 pembayaran, belum lunas
- `cicil` — ada lebih dari 1 pembayaran, belum lunas
- `lunas` — total bayar >= grand total

## Supported Methods
- cash
- transfer

## Supported Receivers
- finance
- office
- field_staff

## Notes
A booking may receive:
- DP (wajib saat create booking)
- installment payment (cicilan)
- final settlement (pelunasan)
- add-on payment
- extend payment

If new charges are added after a booking was marked lunas, the booking may become cicil/dp again until the balance is settled.
