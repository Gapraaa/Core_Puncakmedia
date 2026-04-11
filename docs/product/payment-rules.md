# Payment Rules

## Currency Rule
- All payment values use Rupiah only.
- Store payment amounts as integer Rupiah values.
- Do not use decimal payment amounts and do not display trailing `.00` or `,00`.

## Core Principle
Payments are separate from booking totals.

## Payment Logic
remaining_balance = grand_total - sum(payments.amount)

## Supported Methods
- cash
- transfer

## Supported Receivers
- finance
- office
- field_staff

## Notes
A booking may receive:
- DP
- installment payment
- final settlement
- add-on payment
- extend payment

If new charges are added after a booking was marked fully paid, the booking may become partially unpaid again until the balance is settled.
