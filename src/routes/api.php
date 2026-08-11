<?php

use Illuminate\Support\Facades\Route;
use Modules\Glitter\ReservationPopup\Http\Controllers\Api\Admin\PopupCheckinController as AdminPopupCheckinController;
use Modules\Glitter\ReservationPopup\Http\Controllers\Api\Admin\PopupEventController as AdminPopupEventController;
use Modules\Glitter\ReservationPopup\Http\Controllers\Api\Admin\PopupReservationController as AdminPopupReservationController;
use Modules\Glitter\ReservationPopup\Http\Controllers\Api\Admin\PopupSlotController as AdminPopupSlotController;
use Modules\Glitter\ReservationPopup\Http\Controllers\Api\Auth\PopupReservationController as AuthPopupReservationController;
use Modules\Glitter\ReservationPopup\Http\Controllers\Api\Public\PopupEventController as PublicPopupEventController;

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin', 'throttle:600,1'])
    ->name('admin.')
    ->group(function (): void {
        Route::prefix('events')->name('events.')->group(function (): void {
            Route::get('/', [AdminPopupEventController::class, 'index'])
                ->middleware('permission:admin,glitter-reservation_popup.events.view')
                ->name('index');

            Route::post('/', [AdminPopupEventController::class, 'store'])
                ->middleware('permission:admin,glitter-reservation_popup.events.create')
                ->name('store');

            Route::get('/{event}', [AdminPopupEventController::class, 'show'])
                ->middleware('permission:admin,glitter-reservation_popup.events.view')
                ->name('show');

            Route::put('/{event}', [AdminPopupEventController::class, 'update'])
                ->middleware('permission:admin,glitter-reservation_popup.events.update')
                ->name('update');

            Route::delete('/{event}', [AdminPopupEventController::class, 'destroy'])
                ->middleware('permission:admin,glitter-reservation_popup.events.delete')
                ->name('destroy');

            Route::get('/{event}/slots', [AdminPopupSlotController::class, 'index'])
                ->middleware('permission:admin,glitter-reservation_popup.slots.manage')
                ->name('slots.index');

            Route::post('/{event}/slots/generate', [AdminPopupSlotController::class, 'generate'])
                ->middleware('permission:admin,glitter-reservation_popup.slots.manage')
                ->name('slots.generate');

            Route::put('/{event}/slots/{slot}', [AdminPopupSlotController::class, 'update'])
                ->middleware('permission:admin,glitter-reservation_popup.slots.manage')
                ->name('slots.update');

            Route::get('/{event}/reservations', [AdminPopupReservationController::class, 'eventIndex'])
                ->middleware('permission:admin,glitter-reservation_popup.reservations.view')
                ->name('reservations.index');
        });

        Route::prefix('reservations')->name('reservations.')->group(function (): void {
            Route::get('/', [AdminPopupReservationController::class, 'index'])
                ->middleware('permission:admin,glitter-reservation_popup.reservations.view')
                ->name('index');

            Route::get('/{reservation}', [AdminPopupReservationController::class, 'show'])
                ->middleware('permission:admin,glitter-reservation_popup.reservations.view')
                ->name('show');

            Route::patch('/{reservation}/status', [AdminPopupReservationController::class, 'updateStatus'])
                ->middleware('permission:admin,glitter-reservation_popup.reservations.update')
                ->name('status.update');
        });

        Route::prefix('checkins')->name('checkins.')->group(function (): void {
            Route::get('/', [AdminPopupCheckinController::class, 'index'])
                ->middleware('permission:admin,glitter-reservation_popup.checkins.manage')
                ->name('index');

            Route::post('/', [AdminPopupCheckinController::class, 'store'])
                ->middleware('permission:admin,glitter-reservation_popup.checkins.manage')
                ->name('store');
        });
    });

Route::prefix('events')
    ->middleware(['optional.sanctum', 'throttle:600,1'])
    ->name('events.')
    ->group(function (): void {
        Route::get('/', [PublicPopupEventController::class, 'index'])->name('index');
        Route::get('/{slug}', [PublicPopupEventController::class, 'show'])->name('show');
        Route::get('/{slug}/slots', [PublicPopupEventController::class, 'slots'])->name('slots.index');
    });

Route::prefix('reservations')
    ->middleware(['auth:sanctum', 'throttle:600,1'])
    ->name('reservations.')
    ->group(function (): void {
        Route::post('/', [AuthPopupReservationController::class, 'store'])->name('store');
        Route::get('/me', [AuthPopupReservationController::class, 'myReservations'])->name('me.index');
        Route::get('/{code}', [AuthPopupReservationController::class, 'show'])->name('show');
        Route::post('/{code}/cancel', [AuthPopupReservationController::class, 'cancel'])->name('cancel');
    });
