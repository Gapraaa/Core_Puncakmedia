# Deployment Branch Workflow

Dokumen ini dipakai untuk menetapkan alur branch yang lebih aman untuk deployment Core PMS.

Tujuannya:
- perubahan harian tidak langsung masuk ke branch hosting
- ada ruang untuk uji, review, dan fix sebelum rilis
- branch production tetap lebih stabil

## Branch yang Dipakai

### 1. `staging`
- branch kerja dan upload awal
- semua perubahan fitur, perbaikan, dan polish di-push ke sini dulu
- branch ini dipakai untuk validasi terakhir sebelum rilis

### 2. `main`
- branch produksi / branch yang dipakai hosting
- hanya menerima perubahan yang sudah dianggap aman dan selesai
- jangan langsung coding harian di branch ini

## Alur Kerja yang Disarankan

### Flow harian
1. Kerja di branch `staging`
2. Commit perubahan ke `staging`
3. Push ke `origin/staging`
4. Cek hasilnya
5. Jika masih ada bug, lanjut perbaiki di `staging`

### Flow rilis ke hosting
1. Pastikan `staging` sudah stabil
2. Merge `staging` ke `main`
3. Push `main`
4. Hosting pull atau deploy dari `main`

## Kenapa pakai model ini

Keuntungan:
- `main` lebih bersih
- lebih aman untuk production
- perubahan setengah jadi tidak langsung ikut branch hosting
- lebih gampang rollback kalau ada masalah

Risiko yang berkurang:
- branch hosting ikut membawa eksperimen yang belum selesai
- file migration atau UI yang belum matang langsung tayang di server
- sulit membedakan mana build yang siap tayang dan mana yang masih diuji

## Aturan Praktis

### Branch kerja
- gunakan `staging` sebagai branch utama kerja sekarang
- kalau nanti butuh eksperimen besar, bisa buat branch tambahan dari `staging`

### Branch hosting
- server sebaiknya mengacu ke `main`
- jangan simpan file `.env` production ke repository
- jangan commit file rahasia, credential, atau setting server

### Deploy Laravel
- branch `main` harus selalu siap untuk langkah deploy berikut:
  - `composer install --no-dev --optimize-autoloader`
  - `php artisan migrate --force`
  - `php artisan optimize:clear`
  - `php artisan optimize`
  - `npm run build` atau gunakan hasil build yang sudah disiapkan di pipeline

## Checklist Sebelum Merge ke `main`

- [ ] Fitur utama sudah selesai
- [ ] UI sudah dicek
- [ ] Error penting sudah tidak ada
- [ ] Migration aman dijalankan
- [ ] Test utama lulus
- [ ] Build frontend lulus
- [ ] Tidak ada file eksperimen yang ikut
- [ ] Tidak ada credential atau file sensitif ikut commit

## Checklist Sebelum Deploy Hosting

- [ ] Branch `main` sudah update
- [ ] Backup database siap jika perlu
- [ ] `.env` server benar
- [ ] Queue worker / cache strategy sudah sesuai
- [ ] Storage link dan permission aman
- [ ] Build asset sesuai environment

## Catatan Tambahan

Kalau nanti workflow makin matang, model ini bisa dikembangkan menjadi:
- `feature/*` -> kerja per fitur
- `staging` -> integrasi dan validasi
- `main` -> production

Tapi untuk kondisi sekarang, cukup mulai dari:
- `staging`
- `main`

Itu sudah jauh lebih aman daripada langsung semua perubahan masuk ke branch hosting.
