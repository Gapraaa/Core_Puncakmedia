# AI Context - Puncakmedia Dashboard

## Project
Core PMS for villa management with 3 brands:
- PuncakMediaBogor
- Ngevillayuk
- Kagivilla

This repo is only for **App 1: Core PMS**.

## Stack
- Laravel 12
- MySQL
- TailAdmin
- Blade
- API-ready architecture

## UI / Frontend Rule
- Keep using the existing TailAdmin foundation available in this repository.
- New dashboard pages, forms, tables, cards, modals, and interface elements should follow TailAdmin patterns and styling.
- Do not switch to another admin template or introduce a separate custom design system for the dashboard.
- Extend the current TailAdmin Blade structure and reusable components for Phase 1 implementation.
- Keep the UI consistent with TailAdmin across all core PMS modules.

## Money Rule
- All money values use Rupiah only.
- Store all monetary fields as integer Rupiah values.
- Do not use decimal money storage or display trailing `.00` / `,00`.
- Format displayed money with zero decimal digits.

## Final Domain Decisions
- Use `villas` and `villa_units`
- Use `brands` (tanpa bank_info)
- Use `villa_brand` (pivot)
- Use `bookings`, `booking_items`, and `payments`
- Use `addons`, `seasonal_prices`, and `vouchers`
- Customer does not need an account
- Use guest public link instead of guest login

## Villa Architecture
- Villa biasa (`is_resort=false`): memiliki 1 unit yang otomatis dibuat saat create villa.
  Kapasitas dan harga diisi langsung di form villa.
- Resort (`is_resort=true`): memiliki beberapa unit yang dikelola terpisah via halaman Villa Units.
- Capacity hanya ada di `villa_units`, bukan di `villas`.

## Booking Rules
- Setiap booking yang masuk wajib memiliki DP (payment pertama).
- DP diisi langsung di form create booking.
- `booking_status`: `confirmed`, `cancelled`
- `payment_status`: `dp`, `cicil`, `lunas`
- Semua booking langsung berstatus `confirmed` karena pasti ada DP.
- Pembayaran tambahan (cicilan, pelunasan) dilakukan dari halaman detail booking.
- Tidak ada halaman "Catat Payment" terpisah.
- Payment Ledger (daftar semua payment) tetap ada untuk Finance.

## Pricing Rules
Default pricing fields:
- `price_weekday`
- `price_semi_weekend`
- `price_weekend`

Override pricing:
- `seasonal_prices`

Discount support:
- voucher code
- manual discount with required reason

## Add-on Rules
Add-ons support:
- `per_night`
- `per_stay`

Examples:
- extra bed = per_night
- extra person charge = per_night
- grill package = per_stay
- floaties = per_stay unless specified otherwise

## Payment Rules
Payments are stored separately from bookings.

Possible payment methods:
- cash
- transfer

Possible receivers:
- finance
- office
- field_staff

Each payment should support:
- amount
- method
- receiver
- note
- proof image if needed

Balance logic:
- booking total - total payments = remaining balance

## Important Business Rules
- No DP, no booking confirmation
- Booking may include multiple nights with mixed daily pricing
- Add-ons may be added later and still stay linked to the same booking
- Extend should update booking totals and remaining balance
- A booking can move back to cicil if new add-ons or extend are added after lunas
- Finance needs spreadsheet sync later

## Roles
- Master
- Superadmin
- Head Office
- Finance
- Admin Sales

## Legacy Database
Existing tables include:
- `vilas`
- `reservasi`
- `users`

Migration strategy:
- gradual migration
- preserve old data
- add new structure first
- avoid destructive changes early

## Coding Guidance
- Prefer service classes for business logic
- Keep controllers clean
- Use Form Requests for validation
- Do not place business logic in Blade templates
- Keep names explicit, avoid confusing abbreviations
- Keep dashboard UI implementation aligned with the existing TailAdmin structure and components
