# System Overview

## App 1 - Core PMS
Responsibilities:
- main database
- dashboard
- inventory
- bookings
- booking items
- payments
- finance reporting
- invoice generation
- guest public link
- spreadsheet sync

## App 2 - WhatsApp Gateway
Responsibilities:
- receive WhatsApp messages
- booking draft flow
- AI / parsing
- send data to Core PMS via API
- send notifications

## Integration
App 2 should communicate with App 1 through internal API endpoints.
