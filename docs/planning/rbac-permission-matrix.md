# RBAC Permission Matrix

Dokumen ini adalah draft matriks akses untuk role yang ada di Core PMS saat ini.

Tujuannya:
- jadi acuan sebelum coding RBAC
- membantu diskusi akses per role
- mengurangi kebingungan saat implement menu, route, policy, dan dashboard

## Role Aktif

- `master`
- `superadmin`
- `head-office`
- `finance`
- `admin-sales`

## Keterangan Simbol

- `YA` = boleh
- `LIMITED` = boleh sebagian / view-only / butuh rule tambahan
- `TIDAK` = tidak boleh

## 1. Dashboard

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Dashboard umum | YA | YA | YA | YA | YA |
| Widget operasional | YA | YA | YA | LIMITED | YA |
| Widget keuangan detail | YA | YA | LIMITED | YA | TIDAK |
| Widget audit / sistem | YA | LIMITED | LIMITED | TIDAK | TIDAK |

## 2. Booking

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat daftar booking | YA | YA | YA | YA | YA |
| Lihat detail booking | YA | YA | YA | YA | YA |
| Buat booking | YA | YA | LIMITED | TIDAK | YA |
| Ubah booking | YA | YA | LIMITED | TIDAK | YA |
| Penyesuaian booking | YA | YA | LIMITED | TIDAK | LIMITED |
| Batalkan booking | YA | YA | LIMITED | TIDAK | LIMITED |
| Lihat ringkasan keuangan booking | YA | YA | YA | YA | LIMITED |

## 3. Pembayaran

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat daftar pembayaran | YA | YA | YA | YA | TIDAK |
| Lihat detail pembayaran | YA | YA | YA | YA | TIDAK |
| Tambah pembayaran | YA | YA | LIMITED | YA | TIDAK |
| Edit/koreksi pembayaran | YA | LIMITED | TIDAK | LIMITED | TIDAK |
| Export pembayaran | YA | YA | LIMITED | YA | TIDAK |

## 4. Invoice dan Dokumen

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat daftar invoice | YA | YA | YA | TIDAK | YA |
| Lihat detail invoice | YA | YA | YA | TIDAK | YA |
| Unduh invoice | YA | YA | YA | YA | YA |
| Unduh receipt | YA | YA | YA | YA | YA |
| Split invoice | YA | YA | LIMITED | TIDAK | LIMITED |

## 5. Kalender

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat kalender | YA | YA | YA | TIDAK | YA |
| Buat booking dari kalender | YA | YA | LIMITED | TIDAK | YA |

## 6. Data Master

### Brand

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat brand | YA | YA | YA | TIDAK | TIDAK |
| Tambah/ubah/hapus brand | YA | YA | LIMITED | TIDAK | TIDAK |

### Villa

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat villa | YA | YA | YA | TIDAK | YA |
| Tambah villa | YA | YA | LIMITED | TIDAK | LIMITED |
| Ubah villa | YA | YA | LIMITED | TIDAK | LIMITED |
| Hapus villa | YA | LIMITED | TIDAK | TIDAK | TIDAK |
| Kelola gallery villa | YA | YA | LIMITED | TIDAK | LIMITED |

### Unit Resort

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat unit resort | YA | YA | YA | TIDAK | YA |
| Tambah/ubah unit resort | YA | YA | LIMITED | TIDAK | LIMITED |
| Hapus unit resort | YA | LIMITED | TIDAK | TIDAK | TIDAK |

### Harga High Season

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat harga high season | YA | YA | YA | TIDAK | YA |
| Tambah/ubah harga high season | YA | YA | LIMITED | TIDAK | LIMITED |
| Hapus harga high season | YA | LIMITED | TIDAK | TIDAK | TIDAK |

### Add-ons

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat add-ons | YA | YA | YA | TIDAK | YA |
| Tambah/ubah add-ons | YA | YA | LIMITED | TIDAK | LIMITED |
| Hapus add-ons | YA | LIMITED | TIDAK | TIDAK | TIDAK |

### Voucher

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat voucher | YA | YA | YA | TIDAK | YA |
| Tambah/ubah voucher | YA | YA | LIMITED | TIDAK | LIMITED |
| Hapus voucher | YA | LIMITED | TIDAK | TIDAK | TIDAK |

## 7. User Management

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat user | YA | YA | TIDAK | TIDAK | TIDAK |
| Tambah user | YA | YA | TIDAK | TIDAK | TIDAK |
| Ubah user | YA | YA | TIDAK | TIDAK | TIDAK |
| Reset password user | YA | YA | TIDAK | TIDAK | TIDAK |
| Aktif/nonaktif user | YA | LIMITED | TIDAK | TIDAK | TIDAK |

## 8. Audit Log

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat audit log | YA | YA | YA | TIDAK | TIDAK |
| Export audit log | YA | YA | LIMITED | TIDAK | TIDAK |

## 9. Legacy Migration Tools

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat pemetaan legacy | YA | YA | TIDAK | TIDAK | TIDAK |
| Import dump legacy | YA | TIDAK | TIDAK | TIDAK | TIDAK |
| Jalankan mapping legacy | YA | LIMITED | TIDAK | TIDAK | TIDAK |

## 10. Profil dan Akun Sendiri

| Permission / Area | Master | Superadmin | Head Office | Finance | Admin Sales |
|---|---|---|---|---|---|
| Lihat profil sendiri | YA | YA | YA | YA | YA |
| Ubah profil sendiri | YA | YA | YA | YA | YA |
| Ganti password sendiri | YA | YA | YA | YA | YA |

## Permission Key Draft

Contoh permission key yang bisa dipakai saat implementasi:

### Dashboard
- `dashboard.view`
- `dashboard.finance`
- `dashboard.audit`

### Booking
- `booking.view`
- `booking.create`
- `booking.update`
- `booking.adjust`
- `booking.cancel`

### Payment
- `payment.view`
- `payment.create`
- `payment.update`
- `payment.export`

### Invoice
- `invoice.view`
- `invoice.download`
- `invoice.split`
- `receipt.download`

### Calendar
- `calendar.view`
- `calendar.booking-create`

### Brand
- `brand.view`
- `brand.create`
- `brand.update`
- `brand.delete`

### Villa
- `villa.view`
- `villa.create`
- `villa.update`
- `villa.delete`
- `villa.gallery-manage`

### Villa Unit
- `villa-unit.view`
- `villa-unit.create`
- `villa-unit.update`
- `villa-unit.delete`

### Seasonal Price
- `seasonal-price.view`
- `seasonal-price.create`
- `seasonal-price.update`
- `seasonal-price.delete`

### Add-on
- `addon.view`
- `addon.create`
- `addon.update`
- `addon.delete`

### Voucher
- `voucher.view`
- `voucher.create`
- `voucher.update`
- `voucher.delete`

### User
- `user.view`
- `user.create`
- `user.update`
- `user.reset-password`
- `user.deactivate`

### Audit
- `audit.view`
- `audit.export`

### Legacy
- `legacy.view`
- `legacy.import`
- `legacy.map`

## Catatan Implementasi

- Dokumen ini masih draft kerja
- Beberapa cell `LIMITED` harus diputuskan lagi saat implementasi policy detail
- Jika ada perubahan alur bisnis, update matriks ini dulu sebelum coding besar
