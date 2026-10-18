<?php

namespace Modules\Glitter\ReservationPopup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Glitter\ReservationPopup\Http\Requests\Concerns\UsesReservationPopupValidationMessages;

class IndexAdminPopupReservationsRequest extends FormRequest
{
    use UsesReservationPopupValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'cancelled', 'checked_in', 'no_show'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'keyword' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
