# Legacy DB Mapping

## Existing Legacy Tables
- vilas
- reservasi
- users

## Mapping Direction

### vilas -> villas / villa_units
Possible mapping:
- villa_id -> villas.id or reference mapping
- nama_vila -> villas.name
- lokasi_vila -> villas.location
- detail -> villas.description
- kapasitas_vila -> villas.capacity
- status_villa -> villas.status
- youtube_url -> villas.youtube_url
- kelebihan -> villas.pros
- kekurangan -> villas.cons

Need review:
- units field may help generate villa_units
- harga_vila may need remapping into unit pricing structure

### reservasi -> bookings
Possible mapping:
- vila_id -> villa relation
- unit_code -> villa_units relation
- nama_tamu -> bookings.guest_name
- no_hp -> bookings.guest_phone
- check_in_date -> bookings.check_in
- check_out_date -> bookings.check_out
- total -> bookings.grand_total
- uang_masuk -> bookings.total_paid
- sisa -> bookings.remaining_balance
- status -> bookings.booking_status
- nama_admin -> created_by mapping

### users -> users + roles
Need role migration strategy from legacy role column into final permission structure.