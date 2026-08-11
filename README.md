# Glitter Reservation Popup

`glitter-reservation_popup` is a Glitter.kr bundled module for Gnuboard 7. It provides a JSON Layout based popup event reservation flow without using Laravel 13, Blade, Breeze, or `routes/web.php` centered application structure.

## Module Scope

### v0.1.0

- Popup event management
- Slot management
- User reservation request flow
- User reservation cancellation flow
- QR check-in processing
- Admin management screens through G7 JSON Layout routes
- Controller -> FormRequest -> Service -> RepositoryInterface -> Repository -> Model layering
- Runtime user-facing text through language keys

## Current Status

v0.1.0 is ready for initial deployment.

Implemented:

- Popup event management
- Slot management
- Reservation creation
- Reservation cancellation
- QR check-in
- Admin API
- Admin JSON Layout
- Permission and menu registration
- Module installation and activation

## Verification

Production host verification completed:

- Module install
- Module activate
- Module update
- Admin template integration
- `template_layouts` registration
- API route registration
- Permission registration
- Admin HTTP routes
- API authentication protection
- Guest reservation flow
- Reservation cancellation flow
- Check-in flow
- Duplicate check-in prevention
- Rollback-safe smoke verification

## Unverified

The current production environment has no non-admin user account, so the following flows have not been verified:

- `user_id` based member reservation creation
- My reservations lookup
- Member reservation cancellation

## Production Notes

- API requests should use the `Accept: application/json` header.
- Devtools routes are disabled in the production environment.
- Admin layouts are registered while `sirsoft-admin_basic` is active.

### v0.2.0 or Later

- Waiting list
- Payment flow
- Product inventory coupling
- Reverb realtime notifications

## Repository And Installation

This repository contains only the G7 module source for `glitter-reservation_popup`.

Use one of the following installation targets:

```text
modules/_bundled/glitter-reservation_popup
modules/glitter-reservation_popup
```

Use `modules/_bundled/glitter-reservation_popup` as the editable bundled source path. A deployment ZIP may install the module at `modules/glitter-reservation_popup`.

Install, activate, and update the module through the G7 extension lifecycle. Installed module directories, generated assets, and runtime output are not source files and must not be committed to this repository.
