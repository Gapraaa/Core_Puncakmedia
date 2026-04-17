# Roles and Permissions

## Master
- full access
- technical and data center role
- can manage everything
- akses semua menu dan semua route internal

## Superadmin
- owner-level visibility
- can see detailed dashboard and reports
- can approve exceptional cases
- dapat mengakses hampir semua modul operasional
- tidak ditujukan untuk pekerjaan teknis level Master

## Head Office
- macro operational view
- monitors booking flow, occupancy, team activity
- limited modification rights depending on final policy
- dapat melihat dashboard, booking, invoice, laporan, dan sebagian master data

## Finance
- sees booking totals, payment records, balance status
- verifies payments
- exports reports
- fokus pada invoice, daftar pembayaran, dan laporan keuangan
- tidak mengakses area migration legacy
- tidak mengelola master data utama

## Admin Sales
- handles booking input
- checks availability
- manages guest transaction flow
- uses fast operational shortcuts
- fokus pada booking, invoice, kalender, dan data master operasional
- tidak membuka laporan keuangan penuh dan payment ledger finance

## Implementasi Fase Awal
- auth hanya untuk staff internal, bukan untuk guest/customer
- login menggunakan `username` atau `email` + `password`
- signup publik dinonaktifkan
- semua route utama Core PMS wajib login
- pembatasan akses saat ini masih berbasis role-level access, belum permission per aksi
- tersedia modul `Manajemen User` untuk `master` dan `superadmin`
- tersedia modul `Audit Log` untuk `master`, `superadmin`, dan `head-office`
- user memiliki status `aktif/nonaktif`
- user nonaktif tidak boleh login ke dashboard
- semua aksi penting seperti login, logout, tambah, ubah, hapus, booking, invoice split, penyesuaian booking, dan pembayaran sekarang direkam ke audit log
- akses dokumen penting seperti lihat/unduh invoice dan bukti pembayaran juga dapat direkam untuk kebutuhan audit operasional dan finance

## Mapping Akses Modul Awal
- `Master`: semua modul
- `Superadmin`: semua modul operasional dan laporan
- `Head Office`: dashboard, booking, invoice, laporan, sebagian master data
- `Finance`: dashboard, booking, invoice, daftar pembayaran, laporan keuangan
- `Admin Sales`: dashboard, booking, invoice, kalender, master data operasional
