<?php

namespace Modules\Glitter\ReservationPopup\Http\Requests\Concerns;

trait UsesReservationPopupValidationMessages
{
    public function messages(): array
    {
        return [
            'required' => __('glitter-reservation_popup::messages.validation.required'),
            'required_without' => __('glitter-reservation_popup::messages.validation.required_without'),
            'array' => __('glitter-reservation_popup::messages.validation.array'),
            'string' => __('glitter-reservation_popup::messages.validation.string'),
            'integer' => __('glitter-reservation_popup::messages.validation.integer'),
            'date' => __('glitter-reservation_popup::messages.validation.date'),
            'email' => __('glitter-reservation_popup::messages.validation.email'),
            'max' => __('glitter-reservation_popup::messages.validation.max'),
            'min' => __('glitter-reservation_popup::messages.validation.min'),
            'exists' => __('glitter-reservation_popup::messages.validation.exists'),
            'unique' => __('glitter-reservation_popup::messages.validation.unique'),
            'in' => __('glitter-reservation_popup::messages.validation.in'),
            'timezone' => __('glitter-reservation_popup::messages.validation.timezone'),
            'after' => __('glitter-reservation_popup::messages.validation.after'),
            'after_or_equal' => __('glitter-reservation_popup::messages.validation.after_or_equal'),
            'alpha_dash' => __('glitter-reservation_popup::messages.validation.alpha_dash'),
        ];
    }
}
