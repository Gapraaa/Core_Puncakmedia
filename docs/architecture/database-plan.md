# Database Plan

## Database Engine
Use **MySQL**.

## Money Storage Rule
- All monetary fields use Rupiah only.
- Store all money as integer Rupiah values.
- Do not use decimal money columns for booking, pricing, discount, or payment amounts.
- UI formatting should always use zero decimal digits.
- Recommended column type for money is `unsignedBigInteger`.

## Core Tables

### brands
- id
- name
- slug
- logo
- created_at
- updated_at

### villas
- id
- name
- slug
- location
- description
- is_resort
- status
- rules
- pros
- cons
- youtube_url
- created_at
- updated_at

### villa_units
- id
- villa_id
- unit_name
- unit_type
- capacity
- price_weekday
- price_semi_weekend
- price_weekend
- status
- created_at
- updated_at

### villa_brand
- id
- villa_id
- brand_id
- created_at
- updated_at

### seasonal_prices
- id
- villa_unit_id
- start_date
- end_date
- price
- note
- created_at
- updated_at

### addons
- id
- name
- price
- charge_type
- is_active
- created_at
- updated_at

### vouchers
- id
- code
- discount_type
- amount
- valid_until
- minimum_transaction
- is_active
- created_at
- updated_at

### users
- id
- name
- username
- email
- password
- created_at
- updated_at

### bookings
- id
- invoice_no
- booking_code
- guest_name
- guest_phone
- brand_id
- villa_id
- villa_unit_id
- check_in
- check_out
- total_before_discount
- voucher_id
- voucher_discount_amount
- manual_discount_amount
- manual_discount_reason
- grand_total
- total_paid
- remaining_balance
- payment_status (dp, cicil, lunas)
- booking_status (confirmed, cancelled)
- guest_link_token
- created_by
- created_at
- updated_at

### booking_items
- id
- booking_id
- item_type
- item_name
- reference_date
- quantity
- unit_price
- total_price
- notes
- created_at
- updated_at

### payments
- id
- booking_id
- amount
- payment_method
- received_by
- note
- proof_image
- paid_at
- created_by
- created_at
- updated_at

## Notes
- `booking_items` is required for mixed nightly pricing and add-ons.
- `payments` is required for flexible cash / transfer tracking.
- `remaining_balance` should always be derived from booking total and payment total.
- All listed price and amount fields above should be implemented as integer Rupiah columns.
- `capacity` hanya ada di `villa_units`, bukan di `villas`.
- Villa biasa (`is_resort=false`) otomatis memiliki 1 unit.
- Setiap booking wajib memiliki minimal 1 payment (DP).
