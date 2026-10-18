<?php

namespace Modules\Glitter\ReservationPopup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Glitter\ReservationPopup\Http\Requests\Concerns\UsesReservationPopupValidationMessages;
use Illuminate\Validation\Rule;

class GeneratePopupSlotsRequest extends FormRequest
{
    use UsesReservationPopupValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slots' => ['required', 'array', 'min:1', 'max:200'],
            'slots.*.code' => ['required', 'string', 'max:80'],
            'slots.*.title' => ['nullable', 'array'],
            'slots.*.status' => ['nullable', Rule::in(['open', 'closed', 'hidden'])],
            'slots.*.starts_at' => ['required', 'date'],
            'slots.*.ends_at' => ['required', 'date', 'after:starts_at'],
            'slots.*.capacity' => ['required', 'integer', 'min:1'],
            'slots.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'slots.*.settings' => ['nullable', 'array'],
        ];
    }
}
