# Master TODO

Dokumen ini adalah daftar kerja utama untuk Core PMS.

Fungsi dokumen ini:
- jadi satu pintu untuk melihat semua pekerjaan yang belum selesai
- mencegah arahan tercecer di banyak dokumen
- membantu prioritas kerja harian
- mengarahkan ke dokumen detail kalau butuh pembahasan lebih dalam

## Cara Pakai

- Gunakan file ini sebagai ringkasan utama
- Jika sebuah area punya dokumen detail, buka link yang disediakan
- Jika item selesai, ubah `- [ ]` menjadi `- [x]`
- Jangan hapus item lama, supaya jejak keputusan tetap ada

## Prioritas Terdekat

Kerjakan lebih dulu:
1. security dan pre-deploy hardening
2. migrasi `legacy_reservasi`
3. kesiapan deploy dan rollback
4. CI/CD minimum
5. performance audit dasar

## 1. Security dan Pre-Deploy

Dokumen detail:
- [Security and Pre-Deploy TODO](./security-and-predeploy-todo.md)

Item prioritas tinggi:
- [ ] Tambahkan login throttling
- [ ] Perkuat password policy
- [ ] Hardening session/cookie production
- [ ] Audit authorization booking, payment, invoice, dan dokumen
- [ ] Review ulang akses download invoice dan receipt
- [ ] Siapkan backup dan rollback minimum sebelum deploy production
- [ ] Siapkan checklist smoke test setelah deploy
- [ ] Pastikan `.env` production, `APP_DEBUG`, `APP_KEY`, dan secret lain aman

## 1A. RBAC dan Role Experience

Dokumen detail:
- [RBAC Implementation Plan](./rbac-implementation-plan.md)
- [RBAC Permission Matrix](./rbac-permission-matrix.md)

- [ ] Finalkan role aktif yang dipakai sistem
- [ ] Finalkan permission matrix per modul
- [ ] Implementasikan tabel permissions dan pivot role-permission
- [ ] Tambahkan middleware/gate/policy berbasis permission
- [ ] Rapikan menu dan action button sesuai permission
- [ ] Buat dashboard berbeda per role
- [ ] Evaluasi page/section mana yang perlu beda behavior per role

## 2. Legacy Migration

Dokumen detail:
- [Legacy Vilas Reservasi Migration Plan](./legacy-vilas-reservasi-migration-plan.md)
- [Database Dump Workflow](./database-dump-workflow.md)

Yang sudah ada:
- [x] Import raw `legacy_vilas`
- [x] Import raw `legacy_reservasi`
- [x] Mapping `vilas -> villas/villa_units`

Yang belum:
- [ ] Buat dry-run mapping `reservasi -> bookings`
- [ ] Pisahkan booking valid vs block tanggal
- [ ] Tentukan mapping payment legacy ke payments baru
- [ ] Verifikasi sample hasil migrasi manual
- [ ] Siapkan rollback/import ulang bila mapping salah
- [ ] Putuskan apakah gambar legacy akan dimigrasikan atau diabaikan

## 3. Booking, Payment, dan Invoice

- [ ] Final review flow booking resort/unit
- [ ] Final review tanggal pelunasan otomatis
- [ ] Final review add-ons pada booking
- [ ] Final review riwayat pembayaran per booking
- [ ] Pastikan invoice tetap hanya sebagai dokumen tamu
- [ ] Rapikan akses dan wording invoice di seluruh UI
- [ ] Audit ulang status booking dan status pembayaran agar konsisten

## 4. Villa, Resort, dan Gallery

- [ ] Tambahkan thumbnail cover image di daftar villa
- [ ] Review apakah original image tetap publik atau diprivatkan
- [ ] Strip metadata/EXIF jika perlu
- [ ] Tambahkan limit upload per batch
- [ ] Tambahkan filter status gallery `ready / processing / failed` jika nanti dibutuhkan
- [ ] Siapkan config disk untuk Cloudflare R2 / S3-compatible
- [ ] Dokumentasikan strategi storage lokal vs cloud

## 5. Performance

Dokumen detail:
- [Performance Optimization Plan](./performance-optimization-plan.md)
- [Redis Adoption Plan](./redis-adoption-plan.md)

Prioritas:
- [ ] Pasang Debugbar untuk local profiling
- [ ] Audit N+1 query halaman berat
- [ ] Review index database berdasarkan query nyata
- [ ] Optimasi query kalender
- [ ] Pastikan pagination konsisten di list besar
- [ ] Cache data master yang aman
- [ ] Review queue worker production
- [ ] Siapkan Redis saat VPS siap
- [ ] Terapkan cache aside untuk data master
- [ ] Tentukan key cache yang aman dan yang tidak boleh di-cache
- [ ] Siapkan invalidation manual + TTL
- [ ] Hindari cache untuk area transaksi real-time

## 6. Queue, Worker, dan Background Jobs

- [ ] Tentukan queue connection production final
- [ ] Siapkan supervisor/systemd untuk worker
- [ ] Review retry dan timeout job penting
- [ ] Pindahkan proses berat lain ke queue:
  - [ ] PDF invoice
  - [ ] PDF receipt
  - [ ] WhatsApp
  - [ ] image processing tambahan
- [ ] Siapkan Redis sebagai target queue production saat VPS siap

## 7. Deploy, Branch, dan Rollback

Dokumen detail:
- [Deployment Branch Workflow](./deployment-branch-workflow.md)
- [Laravel Deployment Runbook](./laravel-deployment-runbook.md)

- [x] Workflow `staging -> main`
- [x] Runbook deploy manual
- [ ] Checklist pre-release final
- [ ] Checklist post-deploy smoke test final
- [ ] Prosedur backup database sebelum migration production
- [ ] Prosedur maintenance mode bila migration berisiko
- [ ] Script rollback minimum
- [ ] Verifikasi permission folder server
- [ ] Verifikasi `storage:link`, cache, queue, dan writable path

## 8. CI/CD Readiness

- [ ] Tentukan provider CI/CD
- [ ] Buat pipeline minimum:
  - [ ] install dependency
  - [ ] run tests
  - [ ] build assets
  - [ ] optional static analysis
- [ ] Tambahkan Laravel Pint ke pipeline
- [ ] Tambahkan Larastan ke pipeline
- [ ] Evaluasi Rector setelah test suite stabil
- [ ] Putuskan apakah frontend build dilakukan di CI atau server

## 9. Monitoring dan Audit

- [x] Audit log perubahan data penting
- [x] Audit download dokumen penting
- [ ] Tambahkan channel log production yang jelas
- [ ] Tentukan retensi audit log
- [ ] Tambahkan monitoring error production
- [ ] Siapkan alur cek log jika deploy gagal
- [ ] Evaluasi integrasi error tracking eksternal

## 10. Testing Minimum Sebelum Production

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
- [ ] Dark mode normal
- [ ] Audit log perubahan penting normal
- [ ] Import legacy tidak merusak data aktif

## 11. Roadmap Produk yang Belum Final

Dokumen referensi:
- [Roadmap](./roadmap.md)

- [ ] Finance reporting yang lebih matang
- [ ] Export fitur operasional/finance
- [ ] Guest public link bila memang masih relevan
- [ ] Spreadsheet sync bila masih dibutuhkan
- [ ] Internal API
- [ ] Integrasi WhatsApp gateway

## 12. Optional Hardening dan Peningkatan Lanjut

- [ ] Signed URLs untuk dokumen/file tertentu
- [ ] 2FA untuk role sensitif
- [ ] Device/session management
- [ ] Password expiry untuk role sensitif
- [ ] Approval flow untuk aksi yang sangat sensitif

## Catatan

- Gunakan file ini untuk melihat gambaran besar.
- Untuk area yang lebih kompleks, buka dokumen detailnya.
- Jika ada ide atau keputusan baru, tambahkan dulu di sini supaya tidak hilang, lalu pecah ke dokumen detail kalau sudah matang.
