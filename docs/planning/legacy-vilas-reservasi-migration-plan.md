# Legacy Migration Plan: `vilas` dan `reservasi`

Dokumen ini menjelaskan strategi migrasi data dari database lama ke Core PMS untuk dua tabel utama:
- `vilas` → sumber data villa / resort
- `reservasi` → sumber data booking lama

Tujuan dokumen ini adalah menjaga supaya migrasi:
- aman
- bisa diulang
- tidak merusak data operasional Core PMS yang sudah berjalan

## Kesimpulan Awal

Menurut analisis dump lama, dua tabel ini **layak dipindahkan**, tetapi **jangan langsung import mentah ke tabel utama**.

Alasan:
- struktur lama dan baru tidak 100% sama
- ada field yang harus dipecah
- ada data booking lama yang tampaknya hanya dipakai sebagai block kalender / placeholder
- ada nilai yang perlu dinormalisasi sebelum masuk ke Core PMS

## Struktur Legacy yang Ditemukan

### Tabel `vilas`
Kolom penting:
- `vila_id`
- `nama_vila`
- `villa_type`
- `units` (JSON)
- `lokasi_vila`
- `kapasitas_vila`
- `detail` (JSON)
- `kedalaman_luas_kolam`
- `fasilitas_tambahan_vila`
- `fasilitas_vila` (JSON)
- `harga_villa` (JSON)
- `gambar` (JSON)
- `youtube_url`
- `kelebihan` (JSON)
- `kekurangan` (JSON)
- `status_villa`

### Tabel `reservasi`
Kolom penting:
- `id`
- `vila_id`
- `brand`
- `unit_code`
- `no`
- `nama_tamu`
- `check_in_date`
- `check_out_date`
- `total`
- `uang_masuk`
- `sisa`
- `pelunasan`
- `catatan`
- `no_hp`
- `status`
- `nama_admin`
- `input_by_admin`
- `last_payment`

## Rekomendasi Besar

### Jangan import langsung ke tabel inti
Jangan langsung insert raw dari:
- `vilas`
- `reservasi`

ke:
- `villas`
- `villa_units`
- `bookings`
- `payments`

### Gunakan 2 tahap

#### Tahap 1: staging / raw import
Masukkan data lama dulu ke tabel staging seperti:
- `legacy_vilas`
- `legacy_reservasi`

Isi tabel staging dibuat sangat dekat dengan struktur lama.

#### Tahap 2: transform ke schema Core PMS
Baru setelah itu jalankan command mapping yang mengubah data staging menjadi:
- `villas`
- `villa_units`
- `villa_images`
- `bookings`
- `payments`

## Mapping yang Disarankan

## 1. Mapping `vilas` → `villas`

### Mapping dasar
- `vila_id` → simpan sebagai `legacy_id`
- `nama_vila` → `name`
- `nama_vila` → `slug` (generate slug)
- `lokasi_vila` → `location`
- `youtube_url` → `youtube_url`
- `kapasitas_vila` → `capacity`
- `status_villa` → `status`

### Rule tipe villa
- jika `units` kosong / null → anggap `villa biasa`
- jika `units` berisi data JSON unit → anggap `resort`

### Rule status
Perlu mapping manual/terkontrol:
- `1` → `active`
- `0` → `inactive`

Kalau ada nilai lain, masukkan ke log review.

## 2. Mapping `vilas` → `villa_units`

### Untuk villa biasa
Kalau `units` null:
- buat 1 unit default otomatis
- nama unit bisa mengikuti nama villa
- unit ini jadi unit operasional booking

### Untuk resort
Kalau `units` berisi JSON:
- parse semua unit
- buat record ke `villa_units`
- `unit_code` lama nanti dipakai untuk relasi booking dari `reservasi`

Ini penting karena Core PMS booking berjalan di level `villa_units`.

## 3. Mapping fasilitas

### `fasilitas_vila`
Karena di legacy sudah berupa JSON array, ini cocok dipakai untuk:
- `villa_facilities`

### `fasilitas_tambahan_vila`
Masukkan ke:
- `additional villa facilities`

Tetap perlu dibersihkan:
- null
- string kosong
- typo / variasi huruf besar kecil

## 4. Mapping harga

Legacy `harga_villa` contoh:
- `minggu_kamis`
- `jumat`
- `sabtu`

Mapping yang disarankan ke schema baru:
- `minggu_kamis` → `price_weekday`
- `jumat` → `price_semi_weekend`
- `sabtu` → `price_weekend`

Semua nominal wajib dibersihkan menjadi integer Rupiah.

## 5. Mapping gambar

Legacy `gambar` berupa JSON nama file.

Untuk tahap awal:
- simpan sebagai referensi `legacy image list`
- jangan langsung paksa masuk `villa_images` kalau file fisiknya belum jelas ada di storage baru

Rekomendasi:
- migrasi metadata gambar dulu
- file fisik dan gallery WebP dipisahkan ke fase berikutnya

## 6. Mapping `reservasi` → `bookings`

### Mapping dasar
- `id` → simpan sebagai `legacy_id`
- `no` → opsional sebagai `legacy_booking_no`
- `nama_tamu` → `guest_name`
- `no_hp` → `guest_phone`
- `catatan` → `notes`
- `check_in_date` → `check_in`
- `check_out_date` → `check_out`
- `total` → `grand_total`
- `pelunasan` → `final_payment_due_date`

### Mapping unit
- `vila_id` → cari `villa` hasil migrasi
- `unit_code` → cari `villa_unit` untuk resort
- jika villa biasa dan `unit_code` null → pakai default unit

## 7. Mapping `reservasi` → `payments`

### Legacy payment signals
- `uang_masuk`
- `last_payment`
- `sisa`

Karena struktur lama tidak sebersih ledger baru, rekomendasi paling aman:

#### Opsi aman
- buat **1 payment summary hasil import** dari `uang_masuk`
- beri catatan bahwa ini payment hasil migrasi legacy

#### Opsi lebih agresif
- pecah DP dan last payment menjadi 2 payment

Untuk fase awal, saya lebih rekomendasikan **opsi aman**:
- total payment import = `uang_masuk`
- method default = `transfer` atau `cash` berdasarkan rule fallback
- receiver default = `office`
- tandai dengan metadata `imported_from_legacy = true`

## Data yang Perlu Disaring

Di `reservasi` terlihat ada banyak row seperti:
- `nama_tamu = null`
- `total = null`
- `uang_masuk = null`

Ini sangat mungkin bukan booking penuh, tapi hanya:
- block tanggal
- placeholder okupansi
- marker kalender

### Rekomendasi
Jangan import semua row `reservasi` sebagai booking aktif.

Gunakan rule:

#### Anggap booking valid jika minimal:
- `nama_tamu` ada
- `check_in_date` ada
- `check_out_date` ada
- `total` ada atau `uang_masuk` ada

#### Anggap block/placeholder jika:
- hampir semua field tamu/payment kosong

Untuk data seperti ini, pilih salah satu:
- skip
- atau import sebagai `calendar block` terpisah di fase berikutnya

Untuk fase awal saya rekomendasikan:
- **skip placeholder**
- hanya import booking yang benar-benar valid

## Field Legacy yang Sebaiknya Disimpan

Supaya migrasi aman dan bisa diaudit, tambahkan kolom atau metadata seperti:
- `legacy_source`
- `legacy_id`
- `legacy_payload`

Minimal di `villas` dan `bookings` ada:
- `legacy_id`
- `legacy_source = 'db_pm_data:u358297714_puncakmedia (7).sql'`

## Strategi Implementasi yang Disarankan

### Fase 1
- buat tabel staging:
  - `legacy_vilas`
  - `legacy_reservasi`

### Fase 2
- buat artisan command:
  - `legacy:import-dump`
  - `legacy:map-vilas`
  - `legacy:map-reservasi`

### Fase 3
- buat report hasil mapping:
  - berapa villa berhasil
  - berapa resort/unit berhasil
  - berapa booking valid
  - berapa booking di-skip
  - berapa data butuh review manual

### Fase 4
- baru lakukan import ke tabel inti

## Prioritas Kerja yang Paling Aman

1. migrasi `vilas` dulu
2. validasi hasil `villas` dan `villa_units`
3. setelah mapping villa stabil, baru migrasi `reservasi`
4. payment dari legacy dibuat versi aman dulu sebagai summary payment

## Rekomendasi Praktis

Kalau ditanya “mana yang dipindahkan dulu?”:

### Jawaban saya:
- **ya, pindahkan `vilas` dulu**
- **setelah itu baru `reservasi`**

Kenapa:
- booking lama butuh referensi villa/unit baru yang sudah benar
- kalau villa/unit belum stabil, import `reservasi` akan berantakan

## Next Step yang Disarankan

- Implementasi tabel staging `legacy_vilas` dan `legacy_reservasi`
- Tambahkan kolom `legacy_id` pada model yang perlu
- Buat command import raw dari dump SQL ke staging
- Buat command transform `vilas` ke `villas` dan `villa_units`
- Baru lanjut transform `reservasi` ke `bookings` dan `payments`
