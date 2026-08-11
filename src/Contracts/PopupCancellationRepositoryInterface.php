<?php

namespace Modules\Glitter\ReservationPopup\Contracts;

use Modules\Glitter\ReservationPopup\Models\PopupCancellation;

interface PopupCancellationRepositoryInterface
{
    public function create(array $data): PopupCancellation;

    public function existsForReservation(int $reservationId): bool;
}
