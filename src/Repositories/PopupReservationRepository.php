<?php

namespace Modules\Glitter\ReservationPopup\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\ReservationPopup\Contracts\PopupReservationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupReservation;

class PopupReservationRepository implements PopupReservationRepositoryInterface
{
    private const ACTIVE_STATUSES = ['pending', 'confirmed', 'checked_in'];

    public function create(array $data): PopupReservation
    {
        $model = PopupReservation::query()->create($data);

        return $model->fresh(['event', 'slot']) ?? $model;
    }

    public function update(PopupReservation $reservation, array $data): PopupReservation
    {
        $reservation->fill($data);
        $reservation->save();

        return $reservation->fresh(['event', 'slot']) ?? $reservation;
    }

    public function find(int $id): ?PopupReservation
    {
        return PopupReservation::query()->with(['event', 'slot'])->find($id);
    }

    public function findByCode(string $code): ?PopupReservation
    {
        return PopupReservation::query()
            ->with(['event', 'slot'])
            ->where('reservation_code', $code)
            ->first();
    }

    public function findByCodeForUser(string $code, int $userId): ?PopupReservation
    {
        return PopupReservation::query()
            ->with(['event', 'slot'])
            ->where('reservation_code', $code)
            ->where('user_id', $userId)
            ->first();
    }

    public function findByQrTokenHash(string $hash): ?PopupReservation
    {
        return PopupReservation::query()
            ->with(['event', 'slot'])
            ->where('qr_token_hash', $hash)
            ->first();
    }

    public function existsActiveForCustomer(int $eventId, ?int $userId, string $phone, ?string $email): bool
    {
        return PopupReservation::query()
            ->where('reservation_popup_event_id', $eventId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->where(function ($query) use ($userId, $phone, $email): void {
                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }

                $query->orWhere('customer_phone', $phone);

                if ($email !== null && $email !== '') {
                    $query->orWhere('customer_email', $email);
                }
            })
            ->exists();
    }

    public function countActiveForSlot(int $slotId): int
    {
        return (int) PopupReservation::query()
            ->where('reservation_popup_slot_id', $slotId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->sum('guest_count');
    }

    public function countActiveForEvent(int $eventId): int
    {
        return (int) PopupReservation::query()
            ->where('reservation_popup_event_id', $eventId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->sum('guest_count');
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PopupReservation::query()
            ->with(['event', 'slot'])
            ->orderByDesc('created_at');

        if (! empty($filters['event_id'])) {
            $query->where('reservation_popup_event_id', (int) $filters['event_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($query) use ($keyword): void {
                $query->where('reservation_code', 'like', '%'.$keyword.'%')
                    ->orWhere('customer_name', 'like', '%'.$keyword.'%')
                    ->orWhere('customer_phone', 'like', '%'.$keyword.'%')
                    ->orWhere('customer_email', 'like', '%'.$keyword.'%');
            });
        }

        return $query->paginate(max($perPage, 1));
    }

    public function paginateForEvent(int $eventId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PopupReservation::query()
            ->with(['event', 'slot'])
            ->where('reservation_popup_event_id', $eventId)
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['slot_id'])) {
            $query->where('reservation_popup_slot_id', (int) $filters['slot_id']);
        }

        if (! empty($filters['customer_phone'])) {
            $query->where('customer_phone', 'like', '%'.$filters['customer_phone'].'%');
        }

        return $query->paginate(max($perPage, 1));
    }

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PopupReservation::query()
            ->with(['event', 'slot'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(max($perPage, 1));
    }
}
