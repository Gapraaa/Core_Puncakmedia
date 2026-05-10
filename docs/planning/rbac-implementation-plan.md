# RBAC Implementation Plan

Dokumen ini dipakai untuk merencanakan implementasi RBAC yang lebih matang untuk Core PMS.

Tujuan utamanya:
- membatasi akses sesuai kebutuhan kerja nyata
- membuat tiap role melihat menu, halaman, dan aksi yang relevan saja
- mengurangi risiko akses berlebih
- menyiapkan fondasi untuk dashboard dan page behavior yang berbeda per role

## Kenapa RBAC Perlu Dilanjutkan

Saat ini sistem sudah punya role dasar, tetapi pembatasannya masih dominan di level:
- menu
- route
- sebagian controller flow

Ini sudah cukup untuk fase awal, tapi belum cukup untuk production yang lebih serius karena:
- beberapa role masih melihat halaman yang terlalu mirip
- belum semua aksi sensitif dibatasi per permission
- belum ada permission matrix yang tegas
- belum ada behavior page yang benar-benar disesuaikan per role

## Prinsip RBAC yang Disarankan

Untuk Core PMS ini, RBAC sebaiknya dibagi menjadi 3 lapis:

### 1. Role
Role adalah identitas level kerja pengguna.

Role aktif saat ini:
- `master`
- `superadmin`
- `head-office`
- `finance`
- `admin-sales`

Role tambahan opsional nanti:
- `operasional`
- `reservation-manager`
- `viewer`

### 2. Permission
Permission adalah aksi spesifik yang boleh atau tidak boleh dilakukan.

Contoh:
- `booking.view`
- `booking.create`
- `booking.update`
- `payment.view`
- `payment.create`
- `invoice.download`
- `villa.update`
- `users.manage`

### 3. Page/UI Scope
Walaupun dua role bisa sama-sama boleh membuka halaman, isi yang ditampilkan tidak harus sama.

Contoh:
- finance dan admin-sales sama-sama buka booking
- finance fokus ke nominal, pembayaran, status pelunasan
- admin-sales fokus ke tamu, unit, tanggal, flow transaksi

Jadi tujuan akhir RBAC bukan hanya “boleh buka halaman atau tidak”, tapi juga:
- apa yang dilihat
- apa yang bisa diklik
- section mana yang disembunyikan
- CTA mana yang paling ditonjolkan

## Kondisi Saat Ini

Yang sudah ada:
- login staff internal
- role dasar
- middleware role
- filtering menu per role
- user aktif/nonaktif
- audit log

Yang belum ada:
- tabel permission terpisah
- pivot role-permission yang tegas
- policy per resource
- matriks aksi yang formal
- dashboard berbeda per role
- page scope/section berbeda per role

## Arah Arsitektur yang Disarankan

### Fase 1: Role + Permission Matrix
Tujuan:
- mendefinisikan hak akses final sebelum coding besar

Output:
- daftar role final
- daftar permission final
- mapping permission per role

### Fase 2: Data Layer RBAC
Tujuan:
- menyiapkan struktur database dan model

Output:
- tabel `permissions`
- pivot `permission_role`
- helper user/role/permission

### Fase 3: Backend Enforcement
Tujuan:
- memastikan route dan aksi sensitif benar-benar dibatasi

Output:
- middleware permission
- gate/policy untuk modul penting
- route protection yang lebih granular

### Fase 4: UI Enforcement
Tujuan:
- menyederhanakan UI sesuai kebutuhan role

Output:
- menu per role + permission
- tombol aksi tampil/sembunyi sesuai permission
- section tertentu disembunyikan untuk role tertentu

### Fase 5: Page Behavior per Role
Tujuan:
- membuat experience tiap role lebih relevan

Output:
- dashboard berbeda per role
- penekanan visual berbeda per role
- kemungkinan section booking/payment berbeda per role

## Modul yang Perlu Masuk RBAC

### 1. Dashboard
- siapa yang boleh lihat
- widget apa yang muncul per role

### 2. Booking
- view list
- create
- edit
- adjust
- cancel
- lihat detail

### 3. Pembayaran
- lihat daftar pembayaran
- tambah pembayaran
- edit/batalkan jika nanti didukung
- export data

### 4. Invoice
- lihat invoice
- unduh invoice
- unduh receipt
- split invoice

### 5. Kalender
- lihat kalender
- buat booking dari tanggal kosong

### 6. Data Master
- brand
- villa
- unit resort
- high season
- add-ons
- voucher

### 7. User Management
- lihat user
- tambah user
- edit user
- nonaktifkan user
- reset password

### 8. Audit Log
- lihat audit log
- filter/export audit log

### 9. Legacy Tools
- akses pemetaan legacy
- import dump
- mapping legacy

### 10. Dokumen dan Gallery
- lihat gallery
- upload image
- reorder image
- set cover
- delete image

## Peran Tiap Role Secara Konseptual

### Master
- akses penuh
- teknis dan kontrol penuh sistem
- boleh akses area sensitif, legacy, user management, audit, setting

### Superadmin
- hampir semua akses bisnis
- owner-level visibility
- tidak harus pegang area teknis terdalam seperti master

### Head Office
- fokus monitoring dan koordinasi
- lihat banyak data, ubah sebagian
- tidak perlu akses sensitif teknis penuh

### Finance
- fokus ke booking financial summary, pembayaran, invoice, laporan
- tidak perlu seluruh master data operasional
- tidak perlu legacy import

### Admin Sales
- fokus ke booking, kalender, tamu, invoice tamu, dan data master operasional
- tidak perlu laporan keuangan penuh
- tidak perlu audit log sensitif

## Pendekatan Implementasi yang Aman

Jangan langsung memecah semua halaman jadi halaman terpisah total untuk setiap role.

Urutan yang lebih sehat:
1. permission matrix dulu
2. backend enforcement
3. menu dan tombol sesuai permission
4. dashboard berbeda per role
5. baru kalau benar-benar perlu, pecah page behavior lebih jauh

Ini penting supaya maintenance tidak cepat berat.

## Rekomendasi Teknis

### Struktur dasar
- `roles`
- `permissions`
- `permission_role`
- helper:
  - `hasPermission()`
  - `hasAnyPermission()`
  - `canAccessModule()`

### Enforcement
- middleware `permission`
- gate/policy untuk resource sensitif
- blade helper untuk show/hide action button

### Naming convention permission
Gunakan format:
- `module.action`

Contoh:
- `booking.view`
- `booking.create`
- `booking.update`
- `payment.view`
- `payment.create`
- `invoice.download`
- `gallery.delete`

## Dashboard dan Page Scope per Role

### Dashboard Admin Sales
Fokus:
- booking hari ini
- check-in/check-out terdekat
- okupansi
- shortcut buat booking

### Dashboard Finance
Fokus:
- total masuk
- outstanding
- DP vs lunas
- invoice dan pembayaran terbaru

### Dashboard Head Office
Fokus:
- ringkasan operasional
- performa booking
- status tim/aktivitas

### Dashboard Superadmin/Master
Fokus:
- seluruh ringkasan
- audit
- user/system health

## Deliverable yang Disarankan

### Dokumen
- [Permission Matrix](./rbac-permission-matrix.md)
- update `roles-and-permissions.md`
- update `master-todo.md`

### Kode
- migration permissions
- seed permissions
- middleware permission
- refactor route protection
- role-based dashboard widgets

## Tahap Kerja yang Disarankan

### Tahap 1
- finalkan role aktif
- finalkan permission matrix

### Tahap 2
- implement tabel permissions dan pivot
- buat seeder permission

### Tahap 3
- pasang middleware/gate/policy
- audit seluruh route penting

### Tahap 4
- rapikan menu
- rapikan action button
- rapikan dashboard per role

### Tahap 5
- evaluasi page khusus per role jika memang perlu

## Keputusan yang Direkomendasikan Saat Ini

- gunakan RBAC bertahap, bukan refactor besar sekali jalan
- mulai dari permission matrix formal
- fokus dulu ke modul:
  - booking
  - pembayaran
  - invoice
  - data master
  - user management
  - audit log
- jadikan dashboard per role sebagai target sesudah backend permission stabil
