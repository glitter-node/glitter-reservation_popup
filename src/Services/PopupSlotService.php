<?php

namespace Modules\Glitter\ReservationPopup\Services;

use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;
use Modules\Glitter\ReservationPopup\Contracts\PopupEventRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupReservationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupSlotRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupSlot;

class PopupSlotService
{
    public function __construct(
        private PopupSlotRepositoryInterface $slots,
        private PopupEventRepositoryInterface $events,
        private PopupReservationRepositoryInterface $reservations,
    ) {}

    public function listForEvent(int $eventId): Collection
    {
        if ($this->events->find($eventId) === null) {
            throw new RuntimeException($this->message('events.not_found'));
        }

        return $this->slots->listForEvent($eventId);
    }

    public function paginateForEvent(int $eventId, int $perPage = 15): LengthAwarePaginator
    {
        if ($this->events->find($eventId) === null) {
            throw new RuntimeException($this->message('events.not_found'));
        }

        return $this->slots->paginateForEvent($eventId, $perPage);
    }

    public function createSlot(int $eventId, array $data): PopupSlot
    {
        if ($this->events->find($eventId) === null) {
            throw new RuntimeException($this->message('events.not_found'));
        }

        return $this->slots->create(array_merge($data, [
            'reservation_popup_event_id' => $eventId,
            'status' => $data['status'] ?? 'open',
            'reserved_count' => $data['reserved_count'] ?? 0,
            'checked_in_count' => $data['checked_in_count'] ?? 0,
            'sort_order' => $data['sort_order'] ?? 0,
        ]));
    }

    /**
     * @return array<int, PopupSlot>
     */
    public function generateSlots(int $eventId, array $slotPayloads): array
    {
        $created = [];

        foreach ($slotPayloads as $slotPayload) {
            $created[] = $this->createSlot($eventId, $slotPayload);
        }

        return $created;
    }

    public function updateSlot(int $eventId, int $slotId, array $data): PopupSlot
    {
        $slot = $this->getSlotForEvent($eventId, $slotId);

        return $this->slots->update($slot, $data);
    }

    public function deleteSlot(int $eventId, int $slotId): bool
    {
        return $this->slots->delete($this->getSlotForEvent($eventId, $slotId));
    }

    public function getSlotForEvent(int $eventId, int $slotId): PopupSlot
    {
        $slot = $this->slots->findForEvent($eventId, $slotId);

        if ($slot === null) {
            throw new RuntimeException($this->message('slots.not_found'));
        }

        return $slot;
    }

    public function hasCapacity(PopupSlot $slot, int $guestCount = 1): bool
    {
        if ($slot->status !== 'open') {
            return false;
        }

        $reservedCount = $this->reservations->countActiveForSlot((int) $slot->getKey());

        return ($reservedCount + max($guestCount, 1)) <= (int) $slot->capacity;
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
