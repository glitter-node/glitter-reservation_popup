<?php

namespace Modules\Glitter\ReservationPopup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Glitter\ReservationPopup\Http\Requests\Concerns\UsesReservationPopupValidationMessages;

class StorePopupCheckinRequest extends FormRequest
{
    use UsesReservationPopupValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qr_token' => ['required_without:reservation_code', 'string', 'max:255'],
            'reservation_code' => ['required_without:qr_token', 'string', 'max:40'],
            'method' => ['nullable', 'string', 'max:20'],
            'device_label' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
