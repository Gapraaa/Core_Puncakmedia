# Puncakmedia Dashboard

Core Property Management System (PMS) for villa management across 3 brands:
- PuncakMediaBogor
- Ngevillayuk
- Kagivilla

## Stack
- Laravel 12
- MySQL
- TailAdmin
- Blade-based dashboard

## UI / Frontend Rule
- The dashboard UI must keep using the existing TailAdmin foundation in this repository.
- Layouts, components, forms, tables, cards, modals, badges, and other UI elements should follow TailAdmin patterns and styling.
- Do not replace the project with another admin template or build a separate custom design system.
- New pages and features should be implemented by extending the available TailAdmin Blade structure and reusable elements.
- Keep the interface consistent with TailAdmin across all Phase 1 work.

## Money Rule
- All monetary values in this project use Rupiah only.
- Store money as integer Rupiah values, not decimal values.
- Do not use fractional currency storage such as `.00`.
- Format monetary output with zero decimal digits in the UI.

## Apps
This repository is for **App 1: Core PMS**.

A separate service will handle:
- WhatsApp gateway
- AI assistant / bot
- notifications and automation

## Main Concepts
- Multi-brand villa management
- Villas and villa units
- Booking line-items
- Flexible payment ledger
- Guest public link
- Spreadsheet sync for finance

## Main Roles
- Master
- Superadmin
- Head Office
- Finance
- Admin Sales

## Important Notes
- Use MySQL, not PostgreSQL.
- Use TailAdmin as the main dashboard UI base for this project.
- All dashboard elements should align with the existing TailAdmin components and visual patterns.
- All money fields should be integer Rupiah values with no decimal precision.
- Existing legacy database will be migrated gradually.
- Do not rewrite everything at once.
- Keep business rules documented before implementation.
Core Villa System Puncakmedia
