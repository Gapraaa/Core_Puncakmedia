# Security and Pre-Deploy TODO

Dokumen ini dipakai sebagai checklist utama sebelum Core PMS masuk ke alur deploy yang lebih serius atau CI/CD production.

Tujuannya:
- merangkum area security yang sudah ada
- menandai area yang belum dikerjakan
- mencegah hal penting terlewat sebelum deploy
- jadi sumber checklist yang bisa dicentang bertahap

## Cara Pakai

- Gunakan file ini sebagai checklist aktif
- Jika ada item selesai, ubah `- [ ]` menjadi `- [x]`
- Kalau ada keputusan baru, tambahkan catatan singkat di bawah section terkait
- Jangan hapus item lama; lebih baik checklist tetap punya jejak

## Status Ringkas Saat Ini

Fondasi yang **sudah ada**:
- [x] Login internal staff only
- [x] Role dasar dan pembatasan menu/route
- [x] User aktif/nonaktif
- [x] Audit log perubahan data penting
- [x] PDF invoice dan bukti pembayaran
- [x] Workflow branch `staging -> main`
- [x] Runbook deploy Laravel manual
- [x] Gallery villa dengan queue proses image
- [x] Legacy import staging untuk `vilas` dan `reservasi`

Area yang **belum aman dianggap final production**:
- [ ] Proteksi brute-force login
- [ ] Penguatan password policy
- [ ] Hardening cookie/session production
- [ ] Authorization policy per resource penting
- [ ] Review upload file dan akses original image
- [ ] Finalisasi migrasi booking legacy
- [ ] Checklist rollback dan backup production
- [ ] Monitoring error production
- [ ] CI/CD pipeline dasar

## 1. Environment dan Rahasia

- [ ] Pastikan `.env` production tidak pernah masuk repo
- [ ] Pastikan `APP_ENV=production` di server
- [ ] Pastikan `APP_DEBUG=false` di server
- [ ] Pastikan `APP_URL` production benar
- [ ] Pastikan `APP_KEY` production valid dan unik
- [ ] Pastikan seluruh `DB_*` production benar
- [ ] Pastikan credential API/WA/storage dipisah per environment
- [ ] Siapkan template `.env.example` yang benar-benar relevan dengan app saat ini
- [ ] Dokumentasikan semua env penting untuk production

## 2. Authentication

- [x] Login/logout internal sudah aktif
- [x] Session logout sudah invalidate dan regenerate token
- [x] User nonaktif sudah ditolak login
- [ ] Tambahkan login throttling / rate limit brute-force
- [ ] Tambahkan lock sementara setelah percobaan login gagal berulang
- [ ] Tambahkan notifikasi error login yang aman dan tidak bocorkan detail user
- [ ] Review flow reset password internal
- [ ] Pertimbangkan wajib ganti password untuk user baru / password reset
- [ ] Pertimbangkan 2FA untuk role sensitif di fase lanjut

## 3. Password Policy

- [ ] Ubah minimum password jadi lebih kuat
- [ ] Gunakan rule password Laravel yang lebih aman
- [ ] Wajibkan kombinasi huruf dan angka minimal untuk user internal
- [ ] Review apakah password default demo masih ada di seeder non-local
- [ ] Pastikan akun demo tidak ikut environment production

## 4. Session, Cookie, dan Browser Security

- [ ] Pastikan `SESSION_SECURE_COOKIE=true` di production HTTPS
- [ ] Review `same_site` cookie untuk kebutuhan sistem
- [ ] Review session lifetime sesuai kebutuhan operasional
- [ ] Pastikan session driver production sesuai beban
- [ ] Review apakah remember-me memang dibutuhkan atau tidak
- [ ] Tambahkan header security dasar bila belum ada:
  - [ ] `X-Frame-Options`
  - [ ] `X-Content-Type-Options`
  - [ ] `Referrer-Policy`
  - [ ] `Content-Security-Policy` bertahap

## 5. Authorization dan Role

- [x] Middleware role dasar sudah ada
- [x] Menu sudah difilter per role
- [ ] Audit ulang seluruh route sensitif
- [ ] Tambahkan policy/authorization per resource penting:
  - [ ] booking
  - [ ] pembayaran
  - [ ] invoice/dokumen
  - [ ] audit log
  - [ ] manajemen user
- [ ] Review siapa yang boleh delete data master
- [ ] Review siapa yang boleh split invoice
- [ ] Review siapa yang boleh lihat/unduh dokumen tamu
- [ ] Review siapa yang boleh import data legacy

## 6. Validation dan Input Safety

- [x] Validasi dasar form utama sudah banyak tersedia
- [x] Nominal uang sudah dibatasi di flow booking/pembayaran
- [ ] Audit ulang semua request class untuk rule yang masih longgar
- [ ] Pastikan semua select/ID dari form benar-benar divalidasi existence-nya
- [ ] Review sanitasi string bebas seperti catatan/deskripsi/custom facility
- [ ] Review panjang maksimum field penting agar tidak over-post
- [ ] Tambahkan validasi file upload yang lebih tegas:
  - [ ] mime type
  - [ ] ukuran file
  - [ ] dimensi jika perlu

## 7. File Upload, Gallery, dan Storage

- [x] Gallery villa sudah pakai queue proses image
- [x] Output webp sudah ada
- [x] Struktur storage per villa/image sudah rapi
- [x] Sort order dan cover image sudah ada
- [ ] Review apakah original file perlu tetap publik
- [ ] Pertimbangkan strip metadata/EXIF dari file original atau hasil proses
- [ ] Tambahkan batas jumlah upload per batch
- [ ] Tambahkan retry + cleanup policy untuk image failed yang lebih formal
- [ ] Siapkan adapter disk untuk Cloudflare R2 / S3-compatible
- [ ] Dokumentasikan strategi storage lokal vs cloud

## 8. Database Security dan Data Integrity

- [x] Migrasi database sudah berjalan via Laravel
- [x] Legacy dump sudah masuk staging table
- [ ] Review ulang index untuk tabel berat
- [ ] Audit query N+1 di halaman utama
- [ ] Review foreign key dan cascade yang sensitif
- [ ] Pastikan delete besar tidak merusak histori audit/payment
- [ ] Tentukan strategi soft delete vs hard delete untuk data penting
- [ ] Tambahkan backup policy database sebelum deploy production

## 9. Legacy Migration

- [x] Import raw `legacy_vilas`
- [x] Import raw `legacy_reservasi`
- [x] Mapping `vilas -> villas/villa_units`
- [ ] Buat dry-run mapping `reservasi -> bookings`
- [ ] Pisahkan booking valid vs block tanggal
- [ ] Tentukan mapping payment legacy ke payments baru
- [ ] Tentukan bagaimana status booking lama diterjemahkan ke sistem baru
- [ ] Verifikasi sample data hasil mapping dengan cek manual
- [ ] Siapkan rollback/import ulang yang aman jika mapping salah

## 10. Booking, Payment, Invoice, dan Dokumen

- [x] Booking sudah jadi pusat operasional utama
- [x] Pembayaran sudah direkap ke booking
- [x] Invoice tetap sebagai dokumen tamu
- [ ] Review ulang akses download invoice dan receipt
- [ ] Pastikan nomor dokumen production tidak bentrok
- [ ] Review apakah invoice download perlu audit lebih detail
- [ ] Review apakah PDF generation perlu dipindah penuh ke queue
- [ ] Review apakah file PDF perlu expiry / private access jika nanti public URL dipakai

## 11. Logging, Audit, dan Monitoring

- [x] Audit log perubahan data penting sudah ada
- [x] Download dokumen penting sudah tercatat
- [ ] Tambahkan channel log production yang jelas
- [ ] Tentukan retensi audit log
- [ ] Tambahkan monitoring error production
- [ ] Siapkan alur cek log saat deploy gagal
- [ ] Pertimbangkan integrasi error reporting eksternal

## 12. Performance yang Berhubungan dengan Security dan Stability

- [ ] Audit query berat pakai Debugbar di local
- [ ] Tambahkan pagination konsisten untuk tabel besar
- [ ] Optimasi query kalender
- [ ] Cache data master yang aman
- [ ] Review queue worker production
- [ ] Siapkan Redis saat VPS siap
- [ ] Buat summary/statistik table bila query dashboard terlalu berat

## 13. Queue, Worker, dan Job Reliability

- [x] Queue sudah dipakai untuk proses image
- [ ] Tentukan queue connection production final
- [ ] Pastikan worker restart saat deploy
- [ ] Siapkan supervisor/systemd config worker
- [ ] Review retry dan timeout untuk job berat
- [ ] Pisahkan queue prioritas jika perlu
- [ ] Pertimbangkan queue untuk PDF/WhatsApp/gambar lain

## 14. Deploy dan Rollback

- [x] Branch workflow `staging -> main` sudah ada
- [x] Runbook deploy manual sudah ada
- [ ] Checklist pre-deploy final per rilis
- [ ] Checklist post-deploy smoke test final
- [ ] Script rollback minimum
- [ ] Prosedur backup database sebelum migration production
- [ ] Prosedur maintenance mode jika migration berisiko
- [ ] Verifikasi permission folder server
- [ ] Verifikasi `storage:link` dan writable path

## 15. CI/CD Readiness

- [ ] Tentukan provider CI/CD yang dipakai
- [ ] Buat flow minimal:
  - [ ] install dependency
  - [ ] test
  - [ ] build assets
  - [ ] optional static analysis
- [ ] Tambahkan Laravel Pint ke flow CI
- [ ] Tambahkan Larastan ke flow CI
- [ ] Pertimbangkan Rector hanya setelah test dan static analysis stabil
- [ ] Buat rule branch deploy yang jelas:
  - [ ] `staging` untuk validasi
  - [ ] `main` untuk production
- [ ] Tentukan apakah build frontend dilakukan di CI atau server

## 16. Test Minimum Sebelum Production

- [ ] Login user aktif
- [ ] Login user nonaktif ditolak
- [ ] Booking villa biasa
- [ ] Booking resort/unit
- [ ] Preview harga dan pelunasan
- [ ] Pembayaran DP
- [ ] Pembayaran pelunasan
- [ ] Download invoice
- [ ] Download receipt
- [ ] Upload gallery
- [ ] Retry gallery failed
- [ ] Delete single dan bulk gallery
- [ ] Dark mode tidak rusak
- [ ] Audit log perubahan penting
- [ ] Import legacy tidak merusak data aktif

## 17. Optional Next Hardening

- [ ] CSP bertahap yang lebih ketat
- [ ] Signed URLs untuk dokumen/file tertentu
- [ ] Password expiry untuk role sensitif
- [ ] 2FA untuk master/superadmin/finance
- [ ] Device/session management
- [ ] Approval flow untuk aksi sangat sensitif

## Catatan Tambahan

- Fokus pertama sebelum CI/CD production sebaiknya:
  1. login throttling
  2. password policy
  3. hardening session/cookie production
  4. audit route + policy resource penting
  5. backup/rollback deploy

- Fokus kedua setelah itu:
  1. mapping `reservasi -> bookings/payments`
  2. monitoring dan queue production
  3. R2 / cloud storage
  4. performance tuning
