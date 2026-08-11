<?php

namespace Modules\Glitter\ReservationPopup\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class PopupEventResource extends BaseApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'description' => $this->description,
            'location' => is_array($this->settings) ? ($this->settings['location'] ?? null) : null,
            'period' => [
                'starts_at' => $this->starts_at?->toISOString(),
                'ends_at' => $this->ends_at?->toISOString(),
                'reservation_opens_at' => $this->reservation_opens_at?->toISOString(),
                'reservation_closes_at' => $this->reservation_closes_at?->toISOString(),
            ],
            'status' => $this->status,
            'visibility' => $this->visibility,
            'timezone' => $this->timezone,
            'capacity' => $this->capacity !== null ? (int) $this->capacity : null,
            'max_reservations_per_user' => (int) $this->max_reservations_per_user,
            'slot_count' => (int) ($this->slot_count ?? $this->slots_count ?? 0),
            'reservation_count' => (int) ($this->reservation_count ?? $this->reservations_count ?? 0),
            'slots' => $this->whenLoaded('slots', fn () => PopupSlotResource::collection($this->slots)),
            ...$this->formatTimestamps(),
        ];
    }
}
