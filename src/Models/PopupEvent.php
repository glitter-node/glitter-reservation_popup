<?php

namespace Modules\Glitter\ReservationPopup\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PopupEvent extends Model
{
    use SoftDeletes;

    protected $table = 'reservation_popup_events';

    protected $fillable = [
        'slug',
        'title',
        'summary',
        'description',
        'status',
        'visibility',
        'timezone',
        'reservation_opens_at',
        'reservation_closes_at',
        'starts_at',
        'ends_at',
        'capacity',
        'max_reservations_per_user',
        'settings',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'summary' => 'array',
            'description' => 'array',
            'reservation_opens_at' => 'datetime',
            'reservation_closes_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'integer',
            'max_reservations_per_user' => 'integer',
            'settings' => 'array',
            'metadata' => 'array',
        ];
    }

    public function slots(): HasMany
    {
        return $this->hasMany(PopupSlot::class, 'reservation_popup_event_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(PopupReservation::class, 'reservation_popup_event_id');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(PopupCheckin::class, 'reservation_popup_event_id');
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
