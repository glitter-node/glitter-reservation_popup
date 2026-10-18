<?php

namespace Modules\Glitter\ReservationPopup\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Glitter\ReservationPopup\Models\PopupSlot;

interface PopupSlotRepositoryInterface
{
    public function create(array $data): PopupSlot;

    public function update(PopupSlot $slot, array $data): PopupSlot;

    public function delete(PopupSlot $slot): bool;

    public function find(int $id): ?PopupSlot;

    public function findForEvent(int $eventId, int $slotId): ?PopupSlot;

    public function listForEvent(int $eventId): Collection;

    public function paginateForEvent(int $eventId, int $perPage = 15): LengthAwarePaginator;

    public function incrementReservedCount(PopupSlot $slot, int $amount): void;

    public function decrementReservedCount(PopupSlot $slot, int $amount): void;

    public function incrementCheckedInCount(PopupSlot $slot, int $amount): void;
}
