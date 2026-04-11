# Migration Strategy

## Principle
Do not replace the legacy system abruptly.

## Approach
1. Backup legacy database
2. Document current schema
3. Add new tables gradually
4. Preserve old data
5. Map legacy tables into new structure
6. Refactor application in phases
7. Remove legacy dependencies only after stability

## Important Notes
- Avoid destructive schema changes in the early phase
- Prefer additive migrations first
- Validate all booking and payment calculations carefully