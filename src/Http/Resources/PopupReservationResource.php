<?php

namespace Modules\Glitter\ReservationPopup\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class PopupReservationResource extends BaseApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_code' => $this->reservation_code,
            'event' => $this->whenLoaded('event', fn () => new PopupEventResource($this->event)),
            'slot' => $this->whenLoaded('slot', fn () => new PopupSlotResource($this->slot)),
            'applicant' => [
                'user_id' => $this->user_id,
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
                'email' => $this->customer_email,
                'guest_count' => (int) $this->guest_count,
            ],
            'status' => $this->status,
            'memo' => [
                'request' => $this->request_memo,
                'admin' => $this->admin_memo,
            ],
            'qr' => [
                'has_payload' => filled($this->qr_token_hash),
            ],
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            ...$this->formatTimestamps(),
        ];
    }
}
