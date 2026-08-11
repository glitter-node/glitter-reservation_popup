<?php

namespace Modules\Glitter\ReservationPopup\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopupCancellation extends Model
{
    protected $table = 'reservation_popup_cancellations';

    protected $fillable = [
        'reservation_popup_reservation_id',
        'cancel_code',
        'cancelled_by_type',
        'cancelled_by',
        'reason_code',
        'reason_text',
        'cancelled_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(PopupReservation::class, 'reservation_popup_reservation_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
