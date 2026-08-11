<?php

namespace Modules\Glitter\ReservationPopup\Services;

use Carbon\CarbonInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Modules\Glitter\ReservationPopup\Contracts\PopupEventRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupReservationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupSlotRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupEvent;
use Modules\Glitter\ReservationPopup\Models\PopupReservation;
use Modules\Glitter\ReservationPopup\Models\PopupSlot;

class PopupReservationService
{
    public function __construct(
        private PopupReservationRepositoryInterface $reservations,
        private PopupEventRepositoryInterface $events,
        private PopupSlotRepositoryInterface $slots,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->reservations->paginate($filters, $perPage);
    }

    public function paginateForEvent(int $eventId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if ($this->events->find($eventId) === null) {
            throw new RuntimeException($this->message('events.not_found'));
        }

        return $this->reservations->paginateForEvent($eventId, $filters, $perPage);
    }

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->reservations->paginateForUser($userId, $filters, $perPage);
    }

    public function getReservation(int $reservationId): PopupReservation
    {
        $reservation = $this->reservations->find($reservationId);

        if ($reservation === null) {
            throw new RuntimeException($this->message('reservations.not_found'));
        }

        return $reservation;
    }

    public function getReservationForUser(string $reservationCode, int $userId): PopupReservation
    {
        $reservation = $this->reservations->findByCodeForUser($reservationCode, $userId);

        if ($reservation === null) {
            throw new RuntimeException($this->message('reservations.not_found'));
        }

        return $reservation;
    }

    public function createReservation(array $data): PopupReservation
    {
        $eventId = (int) $data['reservation_popup_event_id'];
        $slotId = isset($data['reservation_popup_slot_id']) ? (int) $data['reservation_popup_slot_id'] : null;
        $guestCount = max((int) ($data['guest_count'] ?? 1), 1);
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $customerEmail = $data['customer_email'] ?? null;

        $event = $this->events->find($eventId);

        if ($event === null) {
            throw new RuntimeException($this->message('events.not_found'));
        }

        $slot = $slotId !== null ? $this->slots->findForEvent($eventId, $slotId) : null;

        if ($slotId !== null && $slot === null) {
            throw new RuntimeException($this->message('slots.not_found'));
        }

        if (! $this->canReserve($event, $slot, $guestCount)) {
            throw new RuntimeException($this->message('reservations.not_available'));
        }

        if ($this->reservations->existsActiveForCustomer($eventId, $userId, (string) $data['customer_phone'], $customerEmail)) {
            throw new RuntimeException($this->message('reservations.duplicate'));
        }

        $qrToken = $this->generateQrToken();
        $reservation = $this->reservations->create(array_merge($data, [
            'reservation_popup_event_id' => $eventId,
            'reservation_popup_slot_id' => $slotId,
            'guest_count' => $guestCount,
            'status' => $data['status'] ?? 'pending',
            'reservation_code' => $data['reservation_code'] ?? $this->generateReservationCode(),
            'qr_token_hash' => $data['qr_token_hash'] ?? hash('sha256', $qrToken),
        ]));

        if ($slot !== null) {
            $this->slots->incrementReservedCount($slot, $guestCount);
        }

        return $reservation;
    }

    public function updateStatus(int $reservationId, string $status, ?string $adminMemo = null, ?int $actorUserId = null): PopupReservation
    {
        $reservation = $this->getReservation($reservationId);

        if (! in_array($status, ['pending', 'confirmed', 'cancelled', 'checked_in', 'no_show'], true)) {
            throw new RuntimeException($this->message('reservations.invalid_status'));
        }

        return $this->reservations->update($reservation, [
            'status' => $status,
            'admin_memo' => $adminMemo ?? $reservation->admin_memo,
            'confirmed_at' => $status === 'confirmed' ? Carbon::now() : $reservation->confirmed_at,
            'cancelled_at' => $status === 'cancelled' ? Carbon::now() : $reservation->cancelled_at,
            'updated_by' => $actorUserId,
        ]);
    }

    public function canReserve(PopupEvent $event, ?PopupSlot $slot = null, int $guestCount = 1, ?CarbonInterface $now = null): bool
    {
        $now ??= Carbon::now($event->timezone ?: $this->defaultTimezone());

        if ($event->status !== 'published' || $event->visibility !== 'public') {
            return false;
        }

        if ($event->reservation_opens_at !== null && $now->lessThan($event->reservation_opens_at)) {
            return false;
        }

        if ($event->reservation_closes_at !== null && $now->greaterThan($event->reservation_closes_at)) {
            return false;
        }

        if ($event->capacity !== null) {
            $eventReservedCount = $this->reservations->countActiveForEvent((int) $event->getKey());

            if (($eventReservedCount + max($guestCount, 1)) > (int) $event->capacity) {
                return false;
            }
        }

        if ($slot === null) {
            return true;
        }

        if ($slot->status !== 'open') {
            return false;
        }

        $slotReservedCount = $this->reservations->countActiveForSlot((int) $slot->getKey());

        return ($slotReservedCount + max($guestCount, 1)) <= (int) $slot->capacity;
    }

    private function generateReservationCode(): string
    {
        return 'RP-'.Str::upper(Str::random(10));
    }

    private function generateQrToken(): string
    {
        return Str::random(64);
    }

    private function defaultTimezone(): string
    {
        if (Container::getInstance()->bound('config')) {
            return (string) config('app.timezone', 'Asia/Seoul');
        }

        return 'Asia/Seoul';
    }

    private function message(string $key): string
    {
        $translationKey = "glitter-reservation_popup::messages.{$key}";

        if (Container::getInstance()->bound('translator')) {
            return __($translationKey);
        }

        return $translationKey;
    }
}
