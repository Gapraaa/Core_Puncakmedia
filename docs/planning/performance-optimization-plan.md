# Performance Optimization Plan

Dokumen ini berisi rencana optimasi performa untuk Core PMS berdasarkan kondisi project saat ini.

Fokus dokumen ini:
- urutan optimasi yang paling masuk akal
- area yang paling berpengaruh ke performa
- tools yang relevan untuk developer workflow
- hal yang perlu dikerjakan sekarang vs nanti

Dokumen ini sengaja dibuat bertahap supaya optimasi tidak dilakukan secara acak atau terlalu cepat pada area yang belum benar-benar terbukti lambat.

## Prinsip Utama

### 1. Ukur dulu, baru optimasi
- Jangan langsung menambah cache, Redis, atau summary table tanpa tahu bottleneck nyata.
- Langkah pertama harus selalu observasi query, payload, dan halaman yang berat.

### 2. Prioritaskan bottleneck operasional
- Booking
- Kalender
- Pembayaran
- Dashboard
- Audit log

Area ini paling sering dipakai admin sales, finance, dan operasional.

### 3. Bedakan optimasi aplikasi dan optimasi developer tooling
- Optimasi aplikasi:
  - query
  - cache
  - queue
  - indexing
  - image compression
- Optimasi developer workflow:
  - Debugbar
  - Pint
  - Larastan
  - Rector
  - Boost

### 4. Fokus desktop-office workload
- App ini adalah sistem internal.
- Target utamanya respons cepat untuk kerja harian admin di laptop/PC.
- Optimasi sebaiknya diarahkan ke kecepatan render halaman data, bukan sekadar skor lab.

## Kondisi Project Saat Ini

### Yang sudah ada
- Laravel 12
- Queue driver default: `database`
- Cache store default: `database`
- PDF memakai `barryvdh/laravel-dompdf`
- Pagination sudah ada di beberapa modul utama
- Beberapa index database utama sudah ada pada:
  - `bookings`
  - `payments`
  - `invoices`
  - `audit_logs`

### Yang sudah terpasang untuk developer
- `laravel/pint`
- `laravel/pail`
- `pestphp/pest`

### Yang belum terpasang
- `barryvdh/laravel-debugbar`
- `larastan/larastan`
- `rector/rector`

### Catatan arsitektur
- Booking adalah pusat transaksi internal.
- Invoice lebih berperan sebagai dokumen tamu.
- Kalender menampilkan banyak villa/unit sekaligus, sehingga berpotensi menjadi salah satu halaman terberat.
- PDF invoice dan bukti pembayaran saat ini masih terhubung langsung ke alur request user.

## Prioritas Implementasi

## Fase 1: Observability dan Quick Wins

Tujuan:
- menemukan bottleneck nyata
- menghilangkan query boros
- mengamankan list besar

### 1. Pasang Laravel Debugbar di local

Tujuan:
- cek N+1 query
- lihat query count dan query time per halaman
- identifikasi collector yang paling relevan

Halaman audit pertama:
- kalender booking
- detail booking
- form booking
- daftar pembayaran
- dashboard
- audit log

Catatan:
- hanya untuk local/development
- jangan dipakai di server publik

### 2. Audit eager loading dan N+1

Target:
- relasi booking
- relasi invoice
- relasi payment
- relasi villa dan unit
- seasonal prices
- add-ons dan opsi add-on

Contoh yang perlu diawasi:
- loop booking yang memanggil relasi tanpa `with()`
- list pembayaran yang memanggil booking, invoice, villa, user secara berulang
- kalender yang mencari booking per unit tanpa batching yang tepat

### 3. Pastikan semua tabel besar memakai pagination

Target tabel:
- daftar booking per villa
- daftar pembayaran
- daftar invoice
- audit log
- user management
- data master yang sudah mulai besar

Catatan:
- pencarian tetap harus `withQueryString()`
- pagination harus konsisten tampil di bawah tabel

### 4. Review dan tambah index database yang benar-benar perlu

Index yang sudah cukup baik:
- `bookings (check_in, check_out)`
- `bookings (brand_id, villa_id, villa_unit_id)`
- `bookings (payment_status, booking_status)`
- `payments (booking_id, paid_at)`
- `payments (payment_method, received_by)`
- `payments (invoice_id, paid_at)`
- `invoices (booking_id, payment_status)`
- `audit_logs (module)`
- `audit_logs (action)`
- `audit_logs (created_at)`

Kandidat tambahan yang layak dievaluasi:
- `audit_logs (user_id, created_at)`
- `audit_logs (subject_type, subject_id)`
- `invoices (booking_id, created_at)`
- `bookings (villa_unit_id, check_in, check_out)` jika query kalender mulai berat
- `booking_items (booking_id, item_type)` jika laporan biaya mulai sering dipakai

Catatan:
- index baru harus berdasarkan query nyata, bukan asumsi

## Fase 2: Optimasi Query dan Data Flow

Tujuan:
- mempercepat halaman berat
- mengurangi query berulang

### 1. Optimasi query kalender

Ini salah satu prioritas tertinggi.

Masalah potensial:
- banyak unit/villa dimuat sekaligus
- tiap kartu melakukan pemrosesan booking harian
- query bisa membengkak jika relasi tidak dirancang batch-friendly

Target optimasi:
- load booking berdasarkan rentang bulan yang sedang dibuka
- jangan load data di luar rentang yang dibutuhkan
- preload booking per `villa_unit_id`
- preload hanya field yang benar-benar dipakai
- pertimbangkan struktur data ringan untuk render kalender

Target hasil:
- 1 halaman kalender tetap nyaman walaupun jumlah villa/unit naik

### 2. Rapikan query dashboard

Target:
- total booking
- total pembayaran
- check-in mendatang
- rekap status pembayaran

Prinsip:
- hindari query count yang diulang di banyak widget
- jika beberapa widget memakai data sama, konsolidasikan

### 3. Rapikan query booking detail

Target:
- booking
- item biaya
- invoice terkait
- payments terkait
- voucher
- relasi brand, villa, unit

Prinsip:
- satu halaman detail booking harus dibuka dengan eager loading yang lengkap
- hindari query tambahan saat render partial

### 4. Rapikan query pembayaran dan invoice

Target:
- daftar pembayaran per villa/unit
- daftar invoice per villa/unit

Prinsip:
- list harus hanya mengambil kolom yang relevan
- relasi utama harus dipreload

## Fase 3: Cache yang Aman

Tujuan:
- mengurangi query untuk data yang jarang berubah

### Kandidat cache awal
- daftar villa aktif
- daftar brand aktif
- daftar voucher aktif
- konfigurasi atau setting umum
- pilihan add-on master

### Aturan cache
- hanya cache data yang jarang berubah
- selalu pikirkan invalidation saat create/update/delete
- jangan cache data transaksi aktif tanpa strategi yang jelas

### Rekomendasi awal
- mulai dari cache list master data
- gunakan TTL sederhana lebih dulu
- setelah stabil baru pertimbangkan cache layer yang lebih agresif

## Fase 4: Queue dan Background Jobs

Tujuan:
- memindahkan proses berat dari request user

### Kandidat pertama masuk queue
- generate PDF invoice
- generate bukti pembayaran
- kirim WhatsApp
- proses gambar atau thumbnail villa

### Kenapa penting
- user tidak perlu menunggu proses berat selesai di browser
- server request lebih ringan
- kegagalan bisa diretry

### Strategi awal
- tetap pakai `database queue` dulu
- pecah job menjadi job terpisah
- siapkan retry dan failed jobs monitoring

## Fase 5: Media Optimization

Tujuan:
- mengurangi ukuran payload dan storage

### Langkah yang disarankan
- compress gambar villa saat upload
- batasi dimensi maksimal gambar
- buat thumbnail untuk listing
- simpan original hanya jika memang perlu

### Dampak
- halaman katalog/listing lebih cepat
- upload lebih stabil
- storage lebih hemat

## Fase 6: Summary Table dan Reporting Layer

Tujuan:
- mempercepat dashboard dan laporan yang berat

### Kapan dilakukan
- setelah query utama bersih
- setelah pola laporan cukup stabil

### Kandidat summary table
- statistik booking harian
- okupansi per villa/unit
- rekap pemasukan per hari/periode
- breakdown status pembayaran

### Catatan
- jangan terlalu cepat membuat summary table
- kalau business rule masih berubah, summary table justru bisa menambah beban maintenance

## Fase 7: Redis dan Infrastruktur Produksi

Tujuan:
- mempercepat cache dan queue di environment production

### Kapan dilakukan
- saat VPS siap
- saat worker background mulai aktif
- saat cache database mulai terasa membebani

### Target penggunaan Redis
- cache
- queue
- lock/transient data

### Arah migrasi
- `CACHE_STORE=redis`
- `QUEUE_CONNECTION=redis`

## Laravel Tooling Recommendation

## 1. Laravel Debugbar

Status:
- belum terpasang

Gunanya:
- audit query
- deteksi N+1
- lihat timing per request

Rekomendasi:
- pasang untuk local saja
- aktifkan hanya saat profiling

## 2. Laravel Pint

Status:
- sudah terpasang

Gunanya:
- konsistensi style code
- mempercepat review

Rekomendasi:
- pertahankan
- jalankan rutin sebelum commit besar

## 3. Larastan

Status:
- belum terpasang

Gunanya:
- static analysis untuk Laravel
- membantu temukan bug logic/type lebih awal

Rekomendasi:
- sangat layak dipasang setelah fase observability dasar
- cocok untuk project yang mulai membesar seperti Core PMS

## 4. Rector

Status:
- belum terpasang

Gunanya:
- refactor otomatis
- bantu modernisasi code

Rekomendasi:
- pasang nanti
- lebih aman setelah:
  - test suite cukup kuat
  - Larastan sudah aktif
  - style code stabil

## 5. Laravel Boost

Status:
- belum terpasang

Gunanya:
- tooling AI/dev workflow
- bukan optimasi runtime app

Rekomendasi:
- opsional
- berguna kalau tim banyak memakai AI coding assistant
- bukan prioritas performa aplikasi

## Rencana Implementasi yang Disarankan

### Tahap Sekarang
1. Pasang Debugbar di local
2. Audit query halaman berat
3. Pastikan pagination konsisten di semua list besar
4. Tambah index hanya berdasarkan hasil audit
5. Optimasi query kalender

### Tahap Berikutnya
1. Cache data master yang jarang berubah
2. Queue-kan PDF dan WhatsApp
3. Compress gambar villa

### Tahap Produksi Lanjutan
1. Redis untuk cache dan queue
2. Summary table untuk dashboard/laporan
3. Larastan
4. Rector

## Checklist Teknis

### Query dan halaman
- [ ] Audit query kalender
- [ ] Audit query dashboard
- [ ] Audit query booking detail
- [ ] Audit query pembayaran
- [ ] Audit query invoice
- [ ] Audit query audit log

### Database
- [ ] Review index tambahan yang diperlukan
- [ ] Uji query paling berat dengan data realistis

### Aplikasi
- [ ] Pastikan semua list besar pakai pagination
- [ ] Tambah cache untuk data master
- [ ] Queue-kan PDF
- [ ] Queue-kan WhatsApp
- [ ] Siapkan kompres gambar villa

### Tooling
- [ ] Pasang Debugbar
- [ ] Siapkan baseline Pint jika perlu
- [ ] Pasang Larastan
- [ ] Evaluasi Rector setelah test suite stabil

## Catatan Penutup

Untuk Core PMS ini, optimasi yang paling masuk akal bukan langsung mulai dari Redis atau summary table.

Urutan yang lebih sehat adalah:
- ukur dulu
- rapikan query
- indexing yang tepat
- pagination
- cache aman
- queue proses berat
- baru naik ke Redis dan reporting layer yang lebih kompleks

Dengan urutan ini, performa meningkat tanpa membuat arsitektur terlalu cepat menjadi rumit.
