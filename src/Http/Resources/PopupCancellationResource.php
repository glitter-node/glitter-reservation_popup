<?php

namespace Modules\Glitter\ReservationPopup\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class PopupCancellationResource extends BaseApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_id' => $this->reservation_popup_reservation_id,
            'cancel_code' => $this->cancel_code,
            'cancelled_by_type' => $this->cancelled_by_type,
            'reason_code' => $this->reason_code,
            'reason_text' => $this->reason_text,
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            ...$this->formatTimestamps(),
        ];
    }
}
