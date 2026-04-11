# Booking Flow

## Standard Flow
1. Admin Sales receives guest inquiry
2. Booking details are prepared
3. Booking is created in Core PMS or via WhatsApp Gateway integration
4. Guest pays DP
5. Payment is recorded
6. Booking becomes confirmed
7. Remaining balance is monitored
8. Guest checks in
9. Booking completes

## Important Rules
- No DP, no booking confirmation
- Booking can include multiple nights with mixed price calculation
- Add-ons can be added before or after initial booking
- Add-ons remain linked to the same booking
- Extend adds new booking items and updates total

## Draft Concept
The WhatsApp service may use draft-confirm flow before final posting into Core PMS.