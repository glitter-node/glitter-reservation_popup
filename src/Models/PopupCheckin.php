<?php

namespace Modules\Glitter\ReservationPopup\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopupCheckin extends Model
{
    protected $table = 'reservation_popup_checkins';

    protected $fillable = [
        'reservation_popup_reservation_id',
        'reservation_popup_event_id',
        'reservation_popup_slot_id',
        'checkin_code',
        'method',
        'status',
        'checked_in_at',
        'checked_in_by',
        'device_label',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(PopupReservation::class, 'reservation_popup_reservation_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(PopupEvent::class, 'reservation_popup_event_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(PopupSlot::class, 'reservation_popup_slot_id');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
