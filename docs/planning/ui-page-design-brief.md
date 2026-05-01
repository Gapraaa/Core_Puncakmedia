# UI Page Design Brief

Dokumen ini dibuat sebagai acuan design/UI untuk Core PMS agar setiap halaman punya tujuan yang jelas, isi yang konsisten, dan flow yang mudah dipahami oleh tim operasional.

Dokumen ini tidak membahas detail backend atau database. Fokusnya adalah:
- halaman apa saja yang ada
- siapa yang memakai halaman tersebut
- isi utama per halaman
- aksi utama yang harus terlihat
- konteks bisnis yang harus terasa di UI

## Prinsip Umum Design

### 1. Booking adalah pusat operasional
- Identitas utama di layar operasional adalah `Kode Booking`.
- Finance dan admin sales membaca transaksi dari booking dan pembayaran.
- Invoice tetap ada, tetapi posisinya sebagai dokumen keluar ke tamu.

### 2. Villa-first dan unit-aware
- Banyak flow dimulai dari pilih villa dulu.
- Untuk resort, sistem harus turun ke unit terlebih dahulu.
- UI harus selalu membedakan dengan jelas:
  - `Villa biasa`
  - `Resort`
  - `Unit resort`

### 3. Uang selalu Rupiah
- Semua nominal ditampilkan dalam format Rupiah.
- Input nominal idealnya konsisten:
  - ada prefix `Rp`
  - ada separator ribuan
  - tidak ada desimal

### 4. Admin sales perlu cepat
- Halaman list dan katalog harus mudah discan.
- Kalender harus mendukung pencarian cepat dengan `Ctrl + F`.
- Tombol utama harus jelas dan tidak terlalu banyak pilihan dalam satu level.

### 5. Finance perlu jelas, bukan ramai
- Ringkasan keuangan harus fokus pada:
  - total
  - total dibayar
  - sisa tagihan
  - status pembayaran
- Hindari menonjolkan informasi yang tidak dipakai harian oleh finance.

## Pola Layout Global

Semua halaman utama sebaiknya mengikuti pola ini:

1. `Breadcrumb / page title`
2. `Header halaman`
   - judul
   - deskripsi singkat
   - 1 sampai 2 aksi utama
3. `Filter / pencarian`
4. `Konten utama`
   - tabel
   - card
   - form
   - kalender
5. `State`
   - kosong
   - loading
   - error
   - success

## Ringkasan Modul

### Core PMS
- Dashboard
- Booking
- Invoice
- Daftar Pembayaran

### Operasional
- Kalender
- Laporan Keuangan
- Pemetaan Legacy

### Sistem
- Login
- Profil
- Manajemen User
- Audit Log

### Master Data
- Brand
- Villa
- Unit Resort
- Harga High Season
- Add-ons
- Voucher

## Detail Halaman

## 1. Login

### Route
- `GET /signin`

### Pengguna
- semua user internal

### Tujuan
- pintu masuk ke sistem internal

### Isi halaman
- logo / identitas Core PMS
- judul login
- deskripsi singkat bahwa login memakai akun internal
- field:
  - username atau email
  - kata sandi
  - remember me
- tombol `Masuk`
- info bantuan lupa password
- area flash message / error login

### Catatan design
- harus sederhana, bersih, dan meyakinkan
- jangan ada social login
- jangan ada sign up publik

## 2. Dashboard

### Route
- `GET /`

### Pengguna
- master
- superadmin
- head-office
- finance
- admin-sales

### Tujuan
- memberi ringkasan kondisi sistem dan operasional saat ini

### Isi halaman
- metric cards:
  - total brand
  - total villa
  - total unit
  - total booking
- sorotan hari ini:
  - check-in mendatang
  - total sisa saldo
  - sebaran DP / cicil / lunas
- status pembayaran booking
- tabel check-in mendatang
- daftar pembayaran terbaru
- daftar booking terbaru

### Aksi utama
- klik ke booking terkait
- klik ke pembayaran terkait

### Catatan design
- dashboard sebaiknya terasa sebagai command center
- informasi perlu cepat dibaca dalam 5-10 detik

## 3. Booking: Daftar Booking

### Route
- `GET /bookings`

### Pengguna
- master
- superadmin
- head-office
- finance
- admin-sales

### Tujuan
- memilih villa terlebih dahulu sebelum melihat daftar booking di dalamnya

### Isi halaman
- judul `Daftar Booking Villa`
- deskripsi singkat
- filter pencarian villa
- tabel katalog villa:
  - nama villa
  - lokasi
  - total reservasi
  - aksi

### Aksi utama
- `Lihat Booking`
- `Buat Booking`

### Catatan design
- ini bukan list booking final, tapi gerbang ke booking per villa
- layout harus terasa seperti katalog operasional

## 4. Booking: Pilih Villa untuk Booking Baru

### Route
- `GET /bookings/buat`

### Pengguna
- master
- superadmin
- head-office
- finance
- admin-sales

### Tujuan
- memulai booking baru dengan memilih villa lebih dulu

### Isi halaman
- mirip dengan daftar villa booking
- bedanya copy dan CTA fokus ke booking baru
- tabel villa:
  - nama
  - lokasi
  - total reservasi
  - aksi utama `Buat Booking`

### Catatan design
- tampilannya boleh serupa dengan halaman daftar booking villa
- tetapi CTA utama harus lebih dominan dan jelas

## 5. Booking: List Booking per Villa

### Route
- `GET /bookings/villas/{villa}`

### Pengguna
- master
- superadmin
- head-office
- finance
- admin-sales

### Tujuan
- melihat semua booking untuk satu villa

### Isi halaman
- judul `Daftar Booking - Nama Villa`
- tombol kembali
- tombol `Buat Booking`
- filter booking:
  - pencarian
  - status pembayaran
  - status booking
  - tanggal check-in
- tabel booking:
  - kode booking
  - tamu
  - unit
  - tanggal
  - total dan sisa
  - status
  - aksi `Detail`

### Catatan design
- ini salah satu halaman inti operasional
- kode booking harus menjadi anchor visual utama
- status pembayaran harus gampang discan

## 6. Booking: Form Booking Baru

### Route
- `GET /bookings/villas/{villa}/create`

### Pengguna
- master
- superadmin
- head-office
- finance
- admin-sales

### Tujuan
- membuat booking baru dalam konteks villa terpilih

### Isi halaman
- informasi konteks villa
- form booking
- field utama:
  - brand
  - unit
  - nama tamu
  - nomor telepon
  - check-in
  - check-out
  - voucher
  - diskon manual
  - markup
  - DP
  - metode pembayaran
  - penerima pembayaran
  - catatan pembayaran
- bagian add-ons:
  - default tersembunyi
  - muncul saat user memilih memakai add-ons
- panel preview kanan:
  - harga per malam
  - add-ons
  - voucher
  - diskon manual
  - markup
  - grand total
  - DP
  - sisa tagihan

### Catatan design
- ini adalah form paling penting dan paling sensitif
- preview kanan harus sangat jelas
- flow input harus terasa ringan walaupun field cukup banyak

## 7. Booking: Detail Booking

### Route
- `GET /bookings/{booking}`

### Pengguna
- master
- superadmin
- head-office
- finance
- admin-sales

### Tujuan
- pusat pembacaan transaksi internal untuk satu booking

### Isi halaman
- header:
  - kode booking
  - nama tamu
  - telepon
- tombol aksi:
  - penyesuaian booking
  - unduh invoice
- ringkasan booking
- ringkasan keuangan booking:
  - subtotal
  - total pembayaran
  - sisa tagihan
  - grand total
- tabel komponen biaya booking
- section invoice:
  - daftar invoice terkait
  - status invoice
  - tombol lihat / unduh invoice
- section pisahkan invoice
- riwayat pembayaran booking
- form tambah pembayaran bila belum lunas

### Catatan design
- halaman ini adalah jantung sistem
- harus terasa kuat, rapi, dan informatif
- informasi keuangan harus lebih dominan daripada dokumen

## 8. Booking: Penyesuaian Booking

### Route
- `GET /bookings/{booking}/adjustments/create`

### Pengguna
- master
- superadmin
- head-office
- finance
- admin-sales

### Tujuan
- menambah perubahan setelah booking dibuat

### Isi halaman
- konteks booking induk
- form adjustment:
  - extend tanggal bila perlu
  - tambah add-on
  - penyesuaian biaya
- preview hasil perubahan

### Catatan design
- harus jelas bahwa ini bukan booking baru
- perubahan harus terlihat sebagai tambahan dari booking existing

## 9. Invoice: Katalog Villa

### Route
- `GET /invoices`

### Pengguna
- master
- superadmin
- head-office
- admin-sales

### Tujuan
- mencari invoice dari jalur villa/unit

### Isi halaman
- filter villa
- tabel daftar villa:
  - nama villa
  - tipe
  - lokasi
  - total unit
  - total booking
  - total invoice
  - aksi

### Catatan design
- invoice bukan pusat finance, tapi tetap penting untuk admin sales
- desainnya boleh sedikit lebih “document-oriented”

## 10. Invoice: Unit Resort

### Route
- `GET /invoices/villas/{villa}/units`

### Tujuan
- jika villa adalah resort, user harus masuk ke unit dulu

### Isi halaman
- informasi resort
- daftar unit
- jumlah booking
- jumlah invoice
- aksi masuk ke daftar invoice unit

## 11. Invoice: List Invoice per Unit

### Route
- `GET /invoices/villas/{villa}/units/{villaUnit}`
- untuk villa biasa diarahkan dari `GET /invoices/villas/{villa}`

### Tujuan
- melihat semua invoice pada satu unit

### Isi halaman
- filter invoice
- tabel invoice:
  - nomor invoice
  - label
  - booking induk
  - tamu
  - status pembayaran
  - nominal
  - aksi detail

## 12. Invoice: Detail Invoice

### Route
- `GET /invoices/{invoice}`

### Tujuan
- melihat dokumen invoice lengkap dan pembayaran yang terkait

### Isi halaman
- header:
  - nomor invoice
  - label
  - nama tamu
- tombol:
  - lihat booking
  - unduh invoice
- informasi invoice
- ringkasan nominal invoice
- tabel item invoice
- riwayat pembayaran invoice

### Catatan design
- harus terasa lebih document-like daripada halaman booking

## 13. Pembayaran: Katalog Villa

### Route
- `GET /payments`

### Pengguna
- master
- superadmin
- head-office
- finance

### Tujuan
- memilih villa dulu sebelum masuk ke riwayat pembayaran

### Isi halaman
- filter villa
- tabel villa:
  - nama villa / resort
  - lokasi
  - tipe
  - unit
  - jumlah pembayaran
  - aksi

### Catatan design
- tone halaman ini harus finance-friendly
- sederhana dan fokus ke ledger

## 14. Pembayaran: Unit Resort

### Route
- `GET /payments/villas/{villa}/units`

### Tujuan
- memilih unit resort sebelum membuka pembayaran

### Isi halaman
- konteks resort
- daftar unit
- jumlah booking
- jumlah pembayaran
- aksi `Lihat Pembayaran`

## 15. Pembayaran: List Pembayaran per Unit

### Route
- `GET /payments/villas/{villa}/units/{villaUnit}`
- untuk villa biasa diarahkan dari `GET /payments/villas/{villa}`

### Tujuan
- melihat ledger pembayaran aktual pada unit tertentu

### Isi halaman
- judul villa / unit
- tombol kembali
- filter pembayaran:
  - pencarian
  - metode
  - diterima oleh
- tabel riwayat pembayaran:
  - kode booking
  - nama tamu
  - tanggal
  - metode
  - penerima
  - jumlah
  - unduh bukti

### Catatan design
- harus terasa seperti ledger internal
- tidak perlu menonjolkan invoice

## 16. Kalender Booking

### Route
- `GET /calendar`

### Pengguna
- master
- superadmin
- head-office
- admin-sales

### Tujuan
- memantau okupansi seluruh villa dan unit resort dalam satu halaman

### Isi halaman
- filter:
  - pencarian
  - brand opsional
- kumpulan card full width
- per card:
  - panel kiri kalender bulanan
  - panel kanan informasi properti
  - nama villa / unit
  - lokasi
  - kapasitas
  - info ringkas
  - tombol aksi
- interaksi tanggal:
  - tanggal terisi buka detail booking
  - tanggal kosong buat booking baru

### Catatan design
- harus sangat nyaman untuk `Ctrl + F`
- resort tampil per unit, bukan per parent resort saja
- kalender adalah fokus visual utama dalam card

## 17. Data Master: Brand

### Route
- `GET /master-data/brands`
- create/edit terpisah

### Tujuan
- mengelola brand operasional

### Halaman yang diperlukan
- list brand
- form tambah brand
- form edit brand

### Isi utama
- nama brand
- slug
- status jika nanti diperlukan

## 18. Data Master: Villa

### Route
- `GET /master-data/villas`

### Tujuan
- mengelola properti induk

### Halaman
- list villa
- form tambah villa
- form edit villa

### Isi utama
- nama
- slug
- lokasi
- tipe resort / non-resort
- relasi brand
- status
- informasi kapasitas / properti yang ditampilkan ke operasional

## 19. Data Master: Unit Resort

### Route
- `GET /master-data/villa-units`
- `GET /master-data/villa-units/villas/{villa}`

### Tujuan
- mengelola unit operasional per villa/resort

### Isi halaman
- katalog resort/villa dulu
- jika dipilih, tampil daftar unit
- per unit:
  - nama unit
  - tipe
  - kapasitas
  - harga dasar
  - status

### Catatan design
- untuk resort, ini sangat penting karena operasional booking ada di unit

## 20. Data Master: Harga High Season

### Route
- `GET /master-data/seasonal-prices`

### Tujuan
- mengelola override harga musiman / high season

### Flow halaman
- daftar villa
- jika resort: pilih unit dulu
- lalu list high season
- create/edit high season

### Isi utama
- periode mulai dan selesai
- harga override
- catatan

## 21. Data Master: Add-ons

### Route
- `GET /master-data/addons`
- `GET /master-data/addons/{addon}`

### Tujuan
- mengelola kategori add-on dan opsi harga

### Flow halaman
- list kategori add-on
- detail kategori
- list opsi add-on
- create/edit opsi

### Isi utama
- kategori add-on
- opsi / variant
- harga
- charge basis
- unit label
- status aktif

### Catatan design
- add-on sekarang harus siap untuk skenario:
  - extra bed
  - tambahan orang
  - paket grill A/B/C
  - catering

## 22. Data Master: Voucher

### Route
- `GET /master-data/vouchers`

### Tujuan
- mengelola voucher diskon yang aktif

### Isi utama
- kode voucher
- jenis diskon
- nominal
- minimum transaksi
- masa berlaku
- status aktif

## 23. Profil

### Route
- `GET /profile`

### Tujuan
- user melihat dan memperbarui data dirinya sendiri

### Isi halaman
- identitas user
- role
- status aktif
- form update profil:
  - nama
  - username
  - email
- form ganti password

## 24. Manajemen User

### Route
- `GET /users`

### Pengguna
- master
- superadmin

### Tujuan
- mengelola user internal

### Halaman
- list user
- create user
- edit user

### Isi utama
- nama
- username
- email
- role
- status aktif / nonaktif
- reset password internal

### Catatan design
- halaman ini harus jelas dan aman
- aksi delete perlu terasa berisiko

## 25. Audit Log

### Route
- `GET /audit-logs`

### Pengguna
- master
- superadmin
- head-office

### Tujuan
- melihat riwayat perubahan data penting dalam sistem

### Isi halaman
- filter:
  - pencarian
  - modul
  - aksi
  - user
  - tanggal
- tabel audit:
  - waktu
  - user
  - modul
  - aksi
  - aktivitas
  - target
- detail before / after
- tombol export CSV

### Catatan design
- harus terasa sebagai halaman kontrol dan audit
- detail perubahan sebaiknya mudah dibuka tanpa membebani tabel utama

## 26. Laporan Keuangan

### Route
- `GET /reports/finance`

### Status
- masih placeholder

### Tujuan design
- nantinya menjadi pusat rekap finance

### Kandidat isi
- total pemasukan per periode
- outstanding booking
- breakdown DP / cicil / lunas
- rekap per villa / brand
- ekspor laporan

## 27. Pemetaan Legacy

### Route
- `GET /migration/legacy`

### Status
- masih placeholder

### Tujuan design
- mendokumentasikan pemetaan antara data lama dan struktur baru

### Kandidat isi
- tabel mapping legacy
- status migrasi
- catatan validasi

## Dokumen Tambahan

Ada dua halaman dokumen yang saat ini lebih berperan sebagai output:
- invoice PDF / print view
- bukti pembayaran PDF / print view

Halaman ini tidak perlu dianggap sebagai page operasional utama, tetapi secara design tetap perlu:
- layout formal
- identitas tamu
- detail booking
- nominal
- branding perusahaan

## Prioritas Design yang Paling Penting

Kalau designer ingin mulai dari yang paling berdampak, urutannya saya sarankan:

1. Login
2. Dashboard
3. Booking list villa
4. Booking form
5. Booking detail
6. Kalender booking
7. Daftar pembayaran
8. Invoice detail
9. Data master villa dan unit
10. Manajemen user dan audit log

## Catatan Penutup

Core PMS ini bukan aplikasi publik. Ini adalah sistem internal operasional dan finance. Karena itu, design yang paling cocok bukan yang terlalu marketing-heavy, melainkan:
- cepat dipakai
- jelas
- stabil
- mudah discan
- kuat untuk data padat

Kalau perlu versi berikutnya, dokumen ini bisa dipecah lagi menjadi:
- wireframe per halaman
- daftar komponen UI reusable
- design priority per role
- mobile behavior per halaman
