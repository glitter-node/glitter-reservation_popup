# Changelog

All notable changes to `glitter-reservation_popup` will be documented in this file.

## [0.1.1] - 2026-08-12

### Fixed

- Added the G7 frontend KO/EN translation sources so module `$t:` keys resolve in admin and user route manifests.
- Fixed raw `glitter-reservation_popup.*` translation keys appearing in the reservation popup admin UI.

### Changed

- Clarified the admin sidebar menu label as `반짝이 예약 팝업 관리` in Korean and `Glitter Reservation Popup Management` in English.

### Added

- Added contract coverage for every `$t:` key referenced by module layouts and route manifests in both locales.

## [0.1.0]

### Added

- Popup events
- Popup slots
- Popup reservations
- Reservation cancellation
- QR check-in
- Admin API
- Admin layouts
- Permissions
- Module lifecycle support

### Verified

- Production install
- Production activate
- Production update
- Route registration
- Permission registration
- Guest reservation smoke flow
- Cancellation flow
- Check-in flow
- Duplicate check-in prevention

### Known Limitations

- Authenticated member reservation flow has not been verified because no non-admin user exists in the validation environment.

### Deferred

- Queueing, payments, product inventory integration, and Reverb realtime notifications are deferred to v0.2.0 or later.
