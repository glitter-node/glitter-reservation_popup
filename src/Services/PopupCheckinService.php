<?php

namespace Modules\Glitter\ReservationPopup\Services;

use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Modules\Glitter\ReservationPopup\Contracts\PopupCheckinRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupReservationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupSlotRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupCheckin;
use Modules\Glitter\ReservationPopup\Models\PopupReservation;

class PopupCheckinService
{
    public function __construct(
        private PopupCheckinRepositoryInterface $checkins,
        private PopupReservationRepositoryInterface $reservations,
        private PopupSlotRepositoryInterface $slots,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->checkins->paginate($filters, $perPage);
    }

    public function checkInByQrToken(string $qrToken, array $data = []): PopupCheckin
    {
        $reservation = $this->reservations->findByQrTokenHash(hash('sha256', $qrToken));

        if ($reservation === null) {
            throw new RuntimeException($this->message('reservations.not_found'));
        }

        return $this->checkInReservation($reservation, $data);
    }

    public function checkInByReservationCode(string $reservationCode, array $data = []): PopupCheckin
    {
        $reservation = $this->reservations->findByCode($reservationCode);

        if ($reservation === null) {
            throw new RuntimeException($this->message('reservations.not_found'));
        }

        return $this->checkInReservation($reservation, $data);
    }

    public function checkInReservation(PopupReservation $reservation, array $data = []): PopupCheckin
    {
        if ($this->checkins->existsForReservation((int) $reservation->getKey())) {
            throw new RuntimeException($this->message('checkins.duplicate'));
        }

        if (! in_array($reservation->status, ['pending', 'confirmed'], true)) {
            throw new RuntimeException($this->message('checkins.not_allowed'));
        }

        $checkin = $this->checkins->create([
            'reservation_popup_reservation_id' => $reservation->getKey(),
            'reservation_popup_event_id' => $reservation->reservation_popup_event_id,
            'reservation_popup_slot_id' => $reservation->reservation_popup_slot_id,
            'checkin_code' => $data['checkin_code'] ?? $this->generateCheckinCode(),
            'method' => $data['method'] ?? 'qr',
            'status' => $data['status'] ?? 'checked_in',
            'checked_in_at' => $data['checked_in_at'] ?? Carbon::now(),
            'checked_in_by' => $data['checked_in_by'] ?? null,
            'device_label' => $data['device_label'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        $this->reservations->update($reservation, ['status' => 'checked_in']);

        if ($reservation->relationLoaded('slot') && $reservation->slot !== null) {
            $this->slots->incrementCheckedInCount($reservation->slot, max((int) $reservation->guest_count, 1));
        }

        return $checkin;
    }

    private function generateCheckinCode(): string
    {
        return 'RPCI-'.Str::upper(Str::random(10));
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
