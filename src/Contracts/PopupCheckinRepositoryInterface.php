<?php

namespace Modules\Glitter\ReservationPopup\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\ReservationPopup\Models\PopupCheckin;

interface PopupCheckinRepositoryInterface
{
    public function create(array $data): PopupCheckin;

    public function existsForReservation(int $reservationId): bool;

    public function findByReservationId(int $reservationId): ?PopupCheckin;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
