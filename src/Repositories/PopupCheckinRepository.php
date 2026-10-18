<?php

namespace Modules\Glitter\ReservationPopup\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\ReservationPopup\Contracts\PopupCheckinRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupCheckin;

class PopupCheckinRepository implements PopupCheckinRepositoryInterface
{
    public function create(array $data): PopupCheckin
    {
        $model = PopupCheckin::query()->create($data);

        return $model->fresh(['reservation', 'event', 'slot']) ?? $model;
    }

    public function existsForReservation(int $reservationId): bool
    {
        return PopupCheckin::query()
            ->where('reservation_popup_reservation_id', $reservationId)
            ->exists();
    }

    public function findByReservationId(int $reservationId): ?PopupCheckin
    {
        return PopupCheckin::query()
            ->where('reservation_popup_reservation_id', $reservationId)
            ->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PopupCheckin::query()
            ->with(['reservation', 'event', 'slot'])
            ->orderByDesc('checked_in_at');

        if (! empty($filters['event_id'])) {
            $query->where('reservation_popup_event_id', (int) $filters['event_id']);
        }

        if (! empty($filters['slot_id'])) {
            $query->where('reservation_popup_slot_id', (int) $filters['slot_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(max($perPage, 1));
    }
}
