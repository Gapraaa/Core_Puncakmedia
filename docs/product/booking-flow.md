# Booking Flow

## Standard Flow
1. Admin Sales receives guest inquiry
2. Booking details are prepared
3. Admin membuka menu "Daftar Booking" yang kini menampilkan Katalog Villa
4. Admin memilih Villa target (klik Tambah secara langsung atau Lihat Detail), masuk ke konteks scoped khusus Villa tersebut
5. Formulir pencatatan Booking akan otomatis terikat pada Villa yang dipilih
6. Booking is created in Core PMS with DP (payment pertama wajib)
7. Booking langsung berstatus `confirmed` + `dp`
8. Remaining balance is monitored
9. Pembayaran tambahan (cicilan) dilakukan dari halaman detail booking
10. Booking menjadi `lunas` saat total bayar >= grand total
11. Guest checks in
12. Booking completes

## Payment Status Flow
- `dp` → baru ada 1 pembayaran, belum lunas
- `cicil` → ada lebih dari 1 pembayaran, belum lunas
- `lunas` → total bayar >= grand total

## Booking Status
- `confirmed` → booking aktif (semua booking yang masuk pasti sudah DP)
- `cancelled` → booking dibatalkan

## Important Rules
- No DP, no booking — setiap create booking wajib isi DP
- Booking can include multiple nights with mixed price calculation
- Add-ons can be added before or after initial booking
- Add-ons remain linked to the same booking
- Extend adds new booking items and updates total
- Jika ada tambahan charge setelah lunas, status berubah kembali ke cicil/dp

## Payment Entry
- DP diinput langsung di form create booking
- Cicilan / pelunasan diinput dari halaman detail booking
- Tidak ada halaman "Catat Payment" terpisah
- Payment Ledger (daftar semua payment) tetap tersedia untuk Finance

## Draft Concept
The WhatsApp service may use draft-confirm flow before final posting into Core PMS.

## Update 2026-04-15
- DP saat create booking tidak boleh lebih besar dari `grand_total`
- Setelah booking berhasil dibuat, sistem otomatis membuat `INVOICE UTAMA`
- Dokumen invoice sekarang dihasilkan sebagai PDF dan langsung terunduh setelah booking berhasil dibuat
- Pembayaran tambahan tidak boleh lebih besar dari sisa tagihan invoice / booking
- Dokumen bukti pembayaran sekarang dihasilkan sebagai PDF dan langsung terunduh setelah pembayaran berhasil dicatat
- Modul `Invoice` terpisah tersedia untuk kebutuhan Sales / Finance dengan alur:
  - daftar villa
  - daftar unit untuk resort
  - daftar invoice
  - detail invoice
- Nama invoice yang dibuat disimpan dalam huruf besar agar konsisten secara operasional

## Update 2026-04-22
- Standar waktu operasional Core PMS sekarang memakai WIB dengan timezone aplikasi `Asia/Jakarta`
- Timestamp untuk booking, pembayaran, audit log, invoice, dan dokumen mengikuti standar waktu Indonesia Barat
