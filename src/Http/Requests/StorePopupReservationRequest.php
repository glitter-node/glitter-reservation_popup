<?php

namespace Modules\Glitter\ReservationPopup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Glitter\ReservationPopup\Http\Requests\Concerns\UsesReservationPopupValidationMessages;

class StorePopupReservationRequest extends FormRequest
{
    use UsesReservationPopupValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservation_popup_event_id' => ['required', 'integer', 'exists:reservation_popup_events,id'],
            'reservation_popup_slot_id' => ['nullable', 'integer', 'exists:reservation_popup_slots,id'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:100'],
            'request_memo' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
