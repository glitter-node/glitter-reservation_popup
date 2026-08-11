<?php

namespace Modules\Glitter\ReservationPopup\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Glitter\ReservationPopup\Contracts\PopupSlotRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupSlot;

class PopupSlotRepository implements PopupSlotRepositoryInterface
{
    public function create(array $data): PopupSlot
    {
        return PopupSlot::query()->create($data);
    }

    public function update(PopupSlot $slot, array $data): PopupSlot
    {
        $slot->fill($data);
        $slot->save();

        return $slot->fresh() ?? $slot;
    }

    public function delete(PopupSlot $slot): bool
    {
        return (bool) $slot->delete();
    }

    public function find(int $id): ?PopupSlot
    {
        return PopupSlot::query()->find($id);
    }

    public function findForEvent(int $eventId, int $slotId): ?PopupSlot
    {
        return PopupSlot::query()
            ->where('reservation_popup_event_id', $eventId)
            ->whereKey($slotId)
            ->first();
    }

    public function listForEvent(int $eventId): Collection
    {
        return PopupSlot::query()
            ->where('reservation_popup_event_id', $eventId)
            ->orderBy('starts_at')
            ->orderBy('sort_order')
            ->get();
    }

    public function paginateForEvent(int $eventId, int $perPage = 15): LengthAwarePaginator
    {
        return PopupSlot::query()
            ->where('reservation_popup_event_id', $eventId)
            ->orderBy('starts_at')
            ->orderBy('sort_order')
            ->paginate(max($perPage, 1));
    }

    public function incrementReservedCount(PopupSlot $slot, int $amount): void
    {
        $slot->increment('reserved_count', max($amount, 0));
    }

    public function decrementReservedCount(PopupSlot $slot, int $amount): void
    {
        $slot->decrement('reserved_count', max($amount, 0));
    }

    public function incrementCheckedInCount(PopupSlot $slot, int $amount): void
    {
        $slot->increment('checked_in_count', max($amount, 0));
    }
}
