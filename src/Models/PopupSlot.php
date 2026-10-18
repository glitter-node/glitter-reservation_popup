<?php

namespace Modules\Glitter\ReservationPopup\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PopupSlot extends Model
{
    use SoftDeletes;

    protected $table = 'reservation_popup_slots';

    protected $fillable = [
        'reservation_popup_event_id',
        'code',
        'title',
        'status',
        'starts_at',
        'ends_at',
        'capacity',
        'reserved_count',
        'checked_in_count',
        'sort_order',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'integer',
            'reserved_count' => 'integer',
            'checked_in_count' => 'integer',
            'sort_order' => 'integer',
            'settings' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(PopupEvent::class, 'reservation_popup_event_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(PopupReservation::class, 'reservation_popup_slot_id');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(PopupCheckin::class, 'reservation_popup_slot_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
