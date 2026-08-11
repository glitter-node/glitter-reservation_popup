<?php

namespace Modules\Glitter\ReservationPopup\Services;

use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Modules\Glitter\ReservationPopup\Contracts\PopupCancellationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupReservationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupSlotRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupCancellation;
use Modules\Glitter\ReservationPopup\Models\PopupReservation;

class PopupCancellationService
{
    public function __construct(
        private PopupCancellationRepositoryInterface $cancellations,
        private PopupReservationRepositoryInterface $reservations,
        private PopupSlotRepositoryInterface $slots,
    ) {}

    public function cancelByReservationCode(string $reservationCode, array $data = []): PopupCancellation
    {
        $reservation = $this->reservations->findByCode($reservationCode);

        if ($reservation === null) {
            throw new RuntimeException($this->message('reservations.not_found'));
        }

        return $this->cancelReservation($reservation, $data);
    }

    public function cancelByReservationCodeForUser(string $reservationCode, int $userId, array $data = []): PopupCancellation
    {
        $reservation = $this->reservations->findByCodeForUser($reservationCode, $userId);

        if ($reservation === null) {
            throw new RuntimeException($this->message('reservations.not_found'));
        }

        return $this->cancelReservation($reservation, array_merge($data, [
            'cancelled_by_type' => 'user',
            'cancelled_by' => $userId,
        ]));
    }

    public function canCancel(PopupReservation $reservation): bool
    {
        if (! in_array($reservation->status, ['pending', 'confirmed'], true)) {
            return false;
        }

        if ($this->cancellations->existsForReservation((int) $reservation->getKey())) {
            return false;
        }

        if ($reservation->expires_at !== null && Carbon::now()->greaterThan($reservation->expires_at)) {
            return false;
        }

        return true;
    }

    private function cancelReservation(PopupReservation $reservation, array $data = []): PopupCancellation
    {
        if (! $this->canCancel($reservation)) {
            throw new RuntimeException($this->message('cancellations.not_allowed'));
        }

        $cancellation = $this->cancellations->create([
            'reservation_popup_reservation_id' => $reservation->getKey(),
            'cancel_code' => $data['cancel_code'] ?? $this->generateCancelCode(),
            'cancelled_by_type' => $data['cancelled_by_type'] ?? 'customer',
            'cancelled_by' => $data['cancelled_by'] ?? null,
            'reason_code' => $data['reason_code'] ?? null,
            'reason_text' => $data['reason_text'] ?? null,
            'cancelled_at' => $data['cancelled_at'] ?? Carbon::now(),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        $this->reservations->update($reservation, [
            'status' => 'cancelled',
            'cancelled_at' => $cancellation->cancelled_at,
        ]);

        if ($reservation->relationLoaded('slot') && $reservation->slot !== null) {
            $this->slots->decrementReservedCount($reservation->slot, max((int) $reservation->guest_count, 1));
        }

        return $cancellation;
    }

    private function generateCancelCode(): string
    {
        return 'RPC-'.Str::upper(Str::random(10));
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
