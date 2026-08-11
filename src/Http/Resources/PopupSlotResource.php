<?php

namespace Modules\Glitter\ReservationPopup\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class PopupSlotResource extends BaseApiResource
{
    public function toArray(Request $request): array
    {
        $capacity = (int) $this->capacity;
        $reserved = (int) $this->reserved_count;

        return [
            'id' => $this->id,
            'event_id' => $this->reservation_popup_event_id,
            'code' => $this->code,
            'title' => $this->title,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'capacity' => $capacity,
            'reserved_count' => $reserved,
            'remaining_count' => max($capacity - $reserved, 0),
            'checked_in_count' => (int) $this->checked_in_count,
            'status' => $this->status,
            'sort_order' => (int) $this->sort_order,
        ];
    }
}
