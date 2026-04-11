# Pricing Rules

## Currency Rule
- All pricing in this project uses Rupiah only.
- Store pricing values as integer Rupiah values.
- Do not use decimal pricing and do not display trailing `.00` or `,00`.

## Default Pricing
Each villa unit has:
- price_weekday
- price_semi_weekend
- price_weekend

## Day Mapping
- weekday: Sunday to Thursday
- semi weekend: Friday
- weekend: Saturday

## Seasonal Override
Use `seasonal_prices` for:
- high season
- holiday periods
- special campaigns
- special date-based pricing

Seasonal price should override default daily pricing for matching dates.

## Discount Rules
Two discount mechanisms:
1. Voucher code
2. Manual discount

Manual discount requires a reason note.
