# Laravel Deployment Runbook

Dokumen ini adalah panduan deploy praktis untuk Core PMS dengan workflow:
- kerja harian di `staging`
- branch hosting di `main`

Dokumen ini sengaja dibuat operasional dan langsung bisa dipakai saat deploy ke server.

## Tujuan

Menjaga supaya deploy Laravel:
- lebih aman
- konsisten
- mudah diulang
- tidak bergantung pada ingatan manual

## Branch Deploy

### `staging`
- tempat push awal
- dipakai untuk cek perubahan
- tempat validasi terakhir sebelum rilis

### `main`
- branch production
- branch yang dipakai hosting
- hanya berisi perubahan yang sudah selesai dan aman

## Flow Rilis yang Disarankan

### 1. Kerja di `staging`
- coding
- test
- build
- cek hasil

### 2. Jika sudah aman
- merge `staging` ke `main`
- push `main`

### 3. Deploy server dari `main`
- server pull branch `main`
- jalankan step deploy Laravel

## Checklist Sebelum Merge `staging` ke `main`

- [ ] fitur sudah selesai
- [ ] bug utama sudah beres
- [ ] migration aman
- [ ] test utama lulus
- [ ] build frontend lulus
- [ ] file eksperimen tidak ikut
- [ ] credential tidak ikut

## Checklist Sebelum Deploy Server

- [ ] branch `main` sudah update
- [ ] backup database siap bila perlu
- [ ] `.env` server sesuai production
- [ ] permission folder Laravel aman
- [ ] queue worker sesuai environment
- [ ] storage link ada

## Struktur Environment yang Disarankan

Yang tidak boleh di-commit:
- `.env`
- credential API
- akses database production
- secret key server

Yang harus dicek di server:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` benar
- `DB_*` benar
- `CACHE_STORE` sesuai environment
- `QUEUE_CONNECTION` sesuai environment

## Urutan Deploy Laravel di Server

Asumsi:
- repo sudah ada di server
- branch production adalah `main`
- deploy dilakukan langsung di folder project server

### 1. Pindah ke branch production

```bash
git checkout main
git pull origin main
```

### 2. Install/update dependency PHP

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Jalankan migration

```bash
php artisan migrate --force
```

### 4. Bersihkan cache lama

```bash
php artisan optimize:clear
```

### 5. Build cache Laravel production

```bash
php artisan optimize
```

### 6. Pastikan storage link ada

```bash
php artisan storage:link
```

Jika sudah pernah dibuat dan aman, langkah ini bisa dilewati.

### 7. Build asset frontend

Kalau build dilakukan langsung di server:

```bash
npm ci
npm run build
```

Kalau build dilakukan di pipeline atau lokal lalu hasil asset ikut terdistribusi, sesuaikan dengan flow yang dipakai.

### 8. Restart queue worker

Kalau memakai queue worker:

```bash
php artisan queue:restart
```

### 9. Reload service bila perlu

Jika memakai supervisor, php-fpm, atau service lain, reload sesuai konfigurasi server.

Contoh umum:

```bash
sudo supervisorctl restart all
sudo systemctl reload php8.2-fpm
```

Sesuaikan dengan environment hosting kamu.

## Urutan Ringkas Deploy

Kalau disingkat, urutan amannya:

```bash
git checkout main
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan storage:link
npm ci
npm run build
php artisan queue:restart
```

## Setelah Deploy

Yang perlu dicek:
- login normal
- dashboard terbuka
- booking bisa dibuka
- create booking normal
- pembayaran normal
- dokumen PDF normal
- asset CSS/JS normal
- tidak ada error 500

## Jika Deploy Gagal

Langkah minimum:
- cek log Laravel
- cek log web server
- cek migration terakhir
- cek cache/config

Perintah cepat:

```bash
php artisan pail
php artisan optimize:clear
```

Kalau perlu rollback cepat:
- kembali ke commit/branch production sebelumnya
- jalankan ulang langkah deploy minimum

## Catatan Khusus untuk Core PMS

Karena app ini punya:
- booking
- pembayaran
- invoice PDF
- audit log
- kalender

Maka area yang paling wajib dicek setelah deploy adalah:
- form booking
- detail booking
- daftar pembayaran
- generate invoice
- dokumen bukti pembayaran

## Pengembangan Berikutnya

Kalau nanti server sudah lebih matang, workflow ini bisa ditingkatkan dengan:
- Redis untuk cache dan queue
- Supervisor khusus queue worker
- CI/CD build pipeline
- deploy script otomatis
- health check setelah deploy

Untuk kondisi sekarang, workflow manual berbasis:
- `staging`
- `main`

sudah cukup aman dan realistis.
