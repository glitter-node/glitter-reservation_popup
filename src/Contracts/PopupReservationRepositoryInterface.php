<?php

namespace Modules\Glitter\ReservationPopup\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\ReservationPopup\Models\PopupReservation;

interface PopupReservationRepositoryInterface
{
    public function create(array $data): PopupReservation;

    public function update(PopupReservation $reservation, array $data): PopupReservation;

    public function find(int $id): ?PopupReservation;

    public function findByCode(string $code): ?PopupReservation;

    public function findByCodeForUser(string $code, int $userId): ?PopupReservation;

    public function findByQrTokenHash(string $hash): ?PopupReservation;

    public function existsActiveForCustomer(int $eventId, ?int $userId, string $phone, ?string $email): bool;

    public function countActiveForSlot(int $slotId): int;

    public function countActiveForEvent(int $eventId): int;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateForEvent(int $eventId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
