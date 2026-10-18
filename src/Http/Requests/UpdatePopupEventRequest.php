<?php

namespace Modules\Glitter\ReservationPopup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Glitter\ReservationPopup\Http\Requests\Concerns\UsesReservationPopupValidationMessages;
use Illuminate\Validation\Rule;

class UpdatePopupEventRequest extends FormRequest
{
    use UsesReservationPopupValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = (int) $this->route('event');

        return [
            'slug' => ['sometimes', 'required', 'string', 'max:120', 'alpha_dash:ascii', Rule::unique('reservation_popup_events', 'slug')->ignore($eventId)],
            'title' => ['sometimes', 'required', 'array'],
            'title.ko' => ['nullable', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'array'],
            'description' => ['nullable', 'array'],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'published', 'archived'])],
            'visibility' => ['sometimes', 'required', Rule::in(['public', 'private', 'unlisted'])],
            'timezone' => ['nullable', 'timezone'],
            'reservation_opens_at' => ['nullable', 'date'],
            'reservation_closes_at' => ['nullable', 'date', 'after_or_equal:reservation_opens_at'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'max_reservations_per_user' => ['nullable', 'integer', 'min:1', 'max:100'],
            'settings' => ['nullable', 'array'],
            'settings.location' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
