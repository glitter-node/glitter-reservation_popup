<?php

namespace Modules\Glitter\ReservationPopup\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class PopupCheckinResource extends BaseApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_id' => $this->reservation_popup_reservation_id,
            'event_id' => $this->reservation_popup_event_id,
            'slot_id' => $this->reservation_popup_slot_id,
            'checkin_code' => $this->checkin_code,
            'method' => $this->method,
            'status' => $this->status,
            'checked_in_at' => $this->checked_in_at?->toISOString(),
            'checked_in_by' => $this->checked_in_by,
            'device_label' => $this->device_label,
            ...$this->formatTimestamps(),
        ];
    }
}
