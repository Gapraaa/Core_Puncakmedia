# Database Dump Workflow

Dokumen ini menjelaskan cara memakai folder [G:\Project\Core_Puncakmedia\db_pm_data](G:\Project\Core_Puncakmedia\db_pm_data) dengan rapi untuk project Core PMS.

## Prinsip Utama

Folder `db_pm_data` **bukan** tempat database MySQL live berjalan.

Folder ini dipakai sebagai:
- tempat simpan file dump `.sql`
- arsip backup database
- sumber restore database lokal / staging

App Laravel tetap membaca database dari konfigurasi `.env`:
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## Kenapa Tidak Langsung Menjalankan Database dari Folder Ini

Karena file:
- `u358297714_puncakmedia (7).sql`

adalah **dump SQL**, bukan engine database aktif.

Artinya:
- file ini bisa di-import ke MySQL
- file ini bisa dijadikan backup
- file ini tidak bisa langsung dipakai Laravel seperti file storage biasa

## Flow yang Direkomendasikan

### 1. Database live tetap di MySQL
Laravel tetap connect ke MySQL biasa sesuai `.env`.

### 2. Semua snapshot database disimpan di `db_pm_data`
Jadi kalau mau:
- backup sebelum perubahan besar
- sinkronisasi data dari hosting
- restore data lokal

semuanya lewat file `.sql` di folder ini.

### 3. Restore/import memakai script project
Sudah disiapkan script:
- [G:\Project\Core_Puncakmedia\scripts\db-import.ps1](G:\Project\Core_Puncakmedia\scripts\db-import.ps1)
- [G:\Project\Core_Puncakmedia\scripts\db-export.ps1](G:\Project\Core_Puncakmedia\scripts\db-export.ps1)

## Script yang Tersedia

### Import dump ke database aktif di `.env`

Script:
- [G:\Project\Core_Puncakmedia\scripts\db-import.ps1](G:\Project\Core_Puncakmedia\scripts\db-import.ps1)

Default:
- ambil file `.sql` terbaru dari `db_pm_data`
- import ke database yang ada di `.env`

Contoh:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\db-import.ps1
```

Kalau database target belum ada:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\db-import.ps1 -CreateDatabase
```

Kalau mau pakai file dump tertentu:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\db-import.ps1 -DumpFile ".\db_pm_data\u358297714_puncakmedia (7).sql"
```

### Export database aktif ke folder `db_pm_data`

Script:
- [G:\Project\Core_Puncakmedia\scripts\db-export.ps1](G:\Project\Core_Puncakmedia\scripts\db-export.ps1)

Contoh:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\db-export.ps1
```

Hasil default:
- file dump baru dengan timestamp
- otomatis masuk ke `db_pm_data`

Contoh nama output:

```text
core_pm_2026-05-06_16-35-10.sql
```

## Kapan Dipakai

### Sebelum perubahan besar
- export dulu database
- simpan snapshot di `db_pm_data`

### Saat pindah device
- ambil dump terbaru
- import ke database lokal device baru

### Saat butuh sinkronisasi dari hosting
- export dari server
- simpan ke `db_pm_data`
- import ke lokal bila perlu

## Rekomendasi Operasional

### Untuk development lokal
- Laravel tetap pakai DB MySQL lokal
- `db_pm_data` jadi tempat backup dan restore

### Untuk staging / production
- jangan jadikan repo sebagai lokasi database live
- tetap pakai service MySQL/MariaDB server normal
- simpan dump hasil backup terpisah dan terjadwal

## Workflow Harian yang Aman

1. Kerja normal di database lokal
2. Kalau mau aman sebelum perubahan besar:
   - jalankan export
3. Kalau mau reset data:
   - jalankan import dari dump yang dipilih
4. Kalau mau sinkron ke device lain:
   - kirim file dump terbaru

## Catatan Penting

- File dump `.sql` bisa besar, jadi tidak selalu ideal untuk di-commit ke Git
- Untuk backup rutin produksi, lebih aman gunakan storage terpisah
- `db_pm_data` cocok untuk kebutuhan lokal, staging, migrasi manual, dan arsip kerja

## Next Step yang Disarankan

- Tambahkan `.gitignore` khusus jika nanti dump besar tidak ingin ikut repo
- Siapkan naming convention dump yang konsisten
- Kalau perlu, buat command artisan wrapper untuk import/export agar lebih mudah bagi tim non-teknis
