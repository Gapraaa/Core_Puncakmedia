# Redis Adoption Plan

Dokumen ini menjelaskan rencana adopsi Redis untuk Core PMS secara bertahap.

Tujuannya:
- memakai Redis hanya di area yang benar-benar berguna
- menghindari cache yang salah sasaran
- menyiapkan pondasi untuk cache, session, dan queue production
- mencegah over-engineering terlalu dini

## Prinsip Dasar

Untuk Core PMS, Redis sebaiknya dipakai **bertahap**, bukan langsung untuk semua hal.

Urutan berpikir yang sehat:
1. ukur bottleneck dulu
2. pilih data yang sering dibaca dan jarang berubah
3. cache data yang hit rate-nya tinggi
4. hindari cache untuk data transaksi yang harus real-time

## Kapan Redis Layak Dipakai

Redis mulai sangat berguna saat:
- data master sering dibaca berulang
- query dashboard mulai berat
- queue background job makin banyak
- session user mulai banyak
- database utama tidak perlu dibebani untuk data yang sama berulang-ulang

## Peran Redis yang Disarankan di Core PMS

### 1. Cache
Redis cocok untuk cache data yang:
- sering dibaca
- jarang berubah
- punya manfaat reuse tinggi

### 2. Session
Redis cocok untuk session production jika:
- user internal mulai banyak
- butuh session store yang cepat
- nanti ada lebih dari satu instance app/server

### 3. Queue
Redis sangat cocok untuk queue jika nanti job mulai bertambah:
- PDF
- WhatsApp
- image processing
- background sync

## Data yang Cocok di-Cache

Untuk sistem ini, kandidat paling aman:

### Data master
- daftar brand aktif
- daftar villa aktif
- daftar villa/unit yang sering dipakai picker
- daftar voucher aktif
- daftar add-ons aktif
- daftar fasilitas master
- setting umum aplikasi

### Data query berat yang stabil
- widget dashboard ringkas
- ringkasan jumlah booking/pembayaran yang tidak harus real-time detik itu juga
- data kalender per bulan jika nanti query-nya memang terbukti berat
- permission/menu matrix per role setelah RBAC matang

### Session
- session user login

## Data yang Tidak Cocok di-Cache

Untuk Core PMS, hindari cache di area ini dulu:
- grand total booking yang sedang aktif di-edit
- payment status yang baru saja berubah
- sisa tagihan real-time
- detail pembayaran yang harus akurat saat itu juga
- audit log terbaru
- data unik per user yang jarang diakses ulang
- hasil pencarian yang sangat dinamis dan liar

Prinsipnya:
- jika stale sedikit saja bisa membingungkan finance atau operasional, jangan cache dulu

## Strategi Cache yang Disarankan

### 1. Cache Aside

Ini strategi paling cocok untuk app ini.

Alur:
1. cek Redis dulu
2. kalau tidak ada, ambil dari database
3. simpan hasil ke Redis
4. kalau data berubah, hapus cache terkait

Cocok untuk:
- villa list
- brand list
- voucher list
- add-on list
- setting umum

Kenapa cocok:
- sederhana
- mudah di-debug
- sangat natural dipakai di Laravel

### 2. TTL + Manual Invalidation

Gunakan dua mekanisme sekaligus:

#### TTL
Sebagai jaring pengaman supaya cache tidak hidup selamanya.

#### Manual invalidation
Saat create/update/delete, hapus cache yang terkait.

Contoh:
- cache `villas.active` TTL 10-30 menit
- saat villa dibuat/diubah/dihapus:
  - hapus `villas.active`
  - hapus `villas.selector`
  - hapus cache lain yang tergantung padanya

## Strategi yang Tidak Disarankan Sekarang

### Write Through

Belum perlu dijadikan strategi utama sekarang.

Alasan:
- app masih berkembang
- cache area transaksi belum stabil
- menambah kompleksitas write path

Cache aside sudah cukup untuk fase saat ini.

### Cache hasil pencarian liar

Hindari dulu untuk:
- pencarian booking yang terlalu dinamis
- filter dengan banyak kombinasi
- data yang hit rate-nya rendah

Kalau tidak, Redis hanya penuh tanpa manfaat nyata.

## Tahap Adopsi Redis yang Disarankan

## Tahap 1: Redis sebagai Pondasi

Saat VPS siap:
- `CACHE_STORE=redis`
- `SESSION_DRIVER=redis`
- `QUEUE_CONNECTION=redis`

Tapi cache aktif yang benar-benar dipakai dulu hanya:
- brand
- villa aktif
- voucher aktif
- add-ons aktif
- fasilitas master
- setting umum

## Tahap 2: Query Ringkas dan Dashboard

Setelah stabil:
- cache widget dashboard tertentu
- cache ringkasan finance yang bukan real-time detik itu juga
- cache menu/permission matrix setelah RBAC matang

## Tahap 3: Kalender dan Query Berat Tertentu

Setelah profiling:
- cache data kalender per bulan per villa/unit bila terbukti layak
- cache query agregasi yang berat dan sering dipakai ulang

## Tahap 4: Redis sebagai Infrastruktur Produksi yang Lebih Serius

Setelah workload naik:
- queue PDF
- queue WhatsApp
- queue job berat lain
- session full di Redis

## Kandidat Cache Key Awal

Contoh key yang aman:
- `brands.active`
- `villas.active`
- `villas.selector`
- `vouchers.active`
- `addons.active`
- `facilities.master`
- `settings.general`
- `dashboard.summary.finance`
- `dashboard.summary.sales`

Key yang sebaiknya dihindari dulu:
- `booking.{id}.balance`
- `invoice.{id}.status`
- `payment.latest`
- hasil pencarian dinamis yang terlalu spesifik

## TTL yang Masuk Akal

Rekomendasi awal:
- data master: `10-30 menit`
- dashboard summary: `1-5 menit`
- session: sesuai kebijakan auth

Catatan:
- TTL bukan pengganti invalidation manual
- TTL hanya pengaman tambahan

## Invalidation yang Perlu Dipikirkan

### Saat brand berubah
Hapus:
- `brands.active`

### Saat villa berubah
Hapus:
- `villas.active`
- `villas.selector`
- cache dashboard/kalender yang bergantung jika nanti ada

### Saat voucher berubah
Hapus:
- `vouchers.active`

### Saat add-on berubah
Hapus:
- `addons.active`

### Saat permission matrix berubah
Hapus:
- cache menu/permission role terkait

## Queue dan Redis

Redis tidak hanya berguna untuk cache.

Untuk Core PMS, Redis juga sangat cocok untuk:
- proses PDF invoice
- proses PDF receipt
- pengiriman WhatsApp
- image processing tambahan
- job integrasi nanti

Jadi Redis nanti sebaiknya dipikirkan sebagai:
- cache store
- session store
- queue backend

## Risiko yang Perlu Dijaga

- cache stale pada data yang seharusnya real-time
- invalidation lupa dijalankan
- terlalu banyak key dinamis yang jarang diakses
- memory Redis penuh karena strategi key buruk
- mengandalkan cache sebelum query inti dibenahi

## Rekomendasi Final untuk Core PMS

### Sekarang
- jangan langsung cache transaksi inti
- fokus dulu ke:
  - cache data master
  - queue
  - session strategy

### Setelah VPS siap
- aktifkan Redis
- pakai cache aside
- kombinasikan TTL dan invalidation manual

### Setelah sistem stabil
- lanjut ke dashboard/query berat
- baru pertimbangkan cache kalender bila memang perlu

## Checklist Implementasi

- [ ] Finalkan area mana saja yang aman di-cache
- [ ] Siapkan config Redis untuk production
- [ ] Aktifkan Redis untuk cache
- [ ] Aktifkan Redis untuk session
- [ ] Aktifkan Redis untuk queue
- [ ] Implementasikan cache aside untuk data master
- [ ] Tambahkan invalidation manual saat create/update/delete master data
- [ ] Tambahkan TTL awal untuk cache key utama
- [ ] Monitoring hit/miss cache jika nanti memungkinkan
- [ ] Review ulang area transaksi yang jangan di-cache

## Kesimpulan

Redis sangat layak untuk Core PMS, tetapi implementasinya harus bertahap.

Urutan yang paling sehat:
1. cache data master
2. pakai Redis untuk session dan queue
3. cache widget/query berat yang reuse-nya tinggi
4. hindari cache area transaksi yang butuh akurasi real-time

Dengan cara ini, Redis membantu performa tanpa membuat perilaku sistem membingungkan untuk admin sales, finance, dan operasional.
