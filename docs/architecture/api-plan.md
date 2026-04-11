# API Plan

## Purpose
Expose internal endpoints for App 2 (WhatsApp Gateway) and possibly future integrations.

## Planned Endpoints
- POST /api/internal/bookings/draft-confirm
- GET /api/internal/villa-units/availability
- GET /api/internal/bookings/{id}
- POST /api/internal/bookings/{id}/payments
- POST /api/internal/bookings/{id}/addons
- POST /api/internal/bookings/{id}/extend

## Notes
- Internal API only
- Must use authentication / token protection
- Keep payload contracts documented before implementation