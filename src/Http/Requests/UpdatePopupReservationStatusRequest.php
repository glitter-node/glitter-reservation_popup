<?php

namespace Modules\Glitter\ReservationPopup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Glitter\ReservationPopup\Http\Requests\Concerns\UsesReservationPopupValidationMessages;
use Illuminate\Validation\Rule;

class UpdatePopupReservationStatusRequest extends FormRequest
{
    use UsesReservationPopupValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled', 'checked_in', 'no_show'])],
            'admin_memo' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
