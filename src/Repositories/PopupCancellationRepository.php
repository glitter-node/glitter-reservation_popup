<?php

namespace Modules\Glitter\ReservationPopup\Repositories;

use Modules\Glitter\ReservationPopup\Contracts\PopupCancellationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupCancellation;

class PopupCancellationRepository implements PopupCancellationRepositoryInterface
{
    public function create(array $data): PopupCancellation
    {
        return PopupCancellation::query()->create($data);
    }

    public function existsForReservation(int $reservationId): bool
    {
        return PopupCancellation::query()
            ->where('reservation_popup_reservation_id', $reservationId)
            ->exists();
    }
}
