<?php

namespace Modules\Glitter\ReservationPopup\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PopupReservation extends Model
{
    use SoftDeletes;

    protected $table = 'reservation_popup_reservations';

    protected $fillable = [
        'reservation_popup_event_id',
        'reservation_popup_slot_id',
        'user_id',
        'reservation_code',
        'qr_token_hash',
        'customer_name',
        'customer_phone',
        'customer_email',
        'guest_count',
        'status',
        'request_memo',
        'admin_memo',
        'confirmed_at',
        'cancelled_at',
        'expires_at',
        'ip_address',
        'user_agent',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'guest_count' => 'integer',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(PopupEvent::class, 'reservation_popup_event_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(PopupSlot::class, 'reservation_popup_slot_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancellations(): HasMany
    {
        return $this->hasMany(PopupCancellation::class, 'reservation_popup_reservation_id');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(PopupCheckin::class, 'reservation_popup_reservation_id');
    }
}
