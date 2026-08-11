<?php

namespace Modules\Glitter\ReservationPopup\Tests\Feature;

require_once __DIR__.'/../ModuleFeatureTestCase.php';

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Glitter\ReservationPopup\Models\PopupCheckin;
use Modules\Glitter\ReservationPopup\Models\PopupEvent;
use Modules\Glitter\ReservationPopup\Models\PopupReservation;
use Modules\Glitter\ReservationPopup\Models\PopupSlot;
use Modules\Glitter\ReservationPopup\Tests\ModuleFeatureTestCase;

class ReservationPopupApiTest extends ModuleFeatureTestCase
{
    public function test_admin_can_create_event(): void
    {
        $payload = [
            'slug' => 'popup-'.Str::lower(Str::random(8)),
            'title' => ['ko' => '테스트 팝업'],
            'status' => 'published',
            'visibility' => 'public',
            'timezone' => 'Asia/Seoul',
            'settings' => ['location' => 'Seoul'],
        ];

        $this->postJson('/api/modules/glitter-reservation_popup/admin/events', $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', $payload['slug']);
    }

    public function test_admin_can_generate_slots(): void
    {
        $event = $this->createEvent();

        $this->postJson("/api/modules/glitter-reservation_popup/admin/events/{$event->id}/slots/generate", [
            'slots' => [[
                'code' => 'slot-1',
                'starts_at' => now()->addDay()->setTime(10, 0)->toDateTimeString(),
                'ends_at' => now()->addDay()->setTime(11, 0)->toDateTimeString(),
                'capacity' => 10,
            ]],
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.code', 'slot-1');
    }

    public function test_public_event_index_only_returns_public_published_events(): void
    {
        $public = $this->createEvent(['slug' => 'public-'.Str::lower(Str::random(6))]);
        $this->createEvent(['slug' => 'draft-'.Str::lower(Str::random(6)), 'status' => 'draft']);

        $this->getJson('/api/modules/glitter-reservation_popup/events')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['slug' => $public->slug])
            ->assertJsonMissing(['status' => 'draft']);
    }

    public function test_authenticated_user_can_create_reservation(): void
    {
        $user = User::factory()->create();
        $event = $this->createEvent();
        $slot = $this->createSlot($event);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/modules/glitter-reservation_popup/reservations', [
                'reservation_popup_event_id' => $event->id,
                'reservation_popup_slot_id' => $slot->id,
                'customer_name' => '홍길동',
                'customer_phone' => '01012345678',
                'customer_email' => 'hong@example.com',
                'guest_count' => 1,
            ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.applicant.user_id', $user->id);
    }

    public function test_user_can_cancel_reservation(): void
    {
        $user = User::factory()->create();
        $event = $this->createEvent();
        $slot = $this->createSlot($event);
        $reservation = $this->createReservation($event, $slot, $user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/modules/glitter-reservation_popup/reservations/{$reservation->reservation_code}/cancel", [
                'reason_text' => '일정 변경',
            ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.reservation_id', $reservation->id);
    }

    public function test_qr_checkin_api_blocks_duplicate_checkin(): void
    {
        $event = $this->createEvent();
        $slot = $this->createSlot($event);
        $reservation = $this->createReservation($event, $slot);

        PopupCheckin::query()->create([
            'reservation_popup_reservation_id' => $reservation->id,
            'reservation_popup_event_id' => $event->id,
            'reservation_popup_slot_id' => $slot->id,
            'checkin_code' => 'RPCI-EXISTS',
            'method' => 'qr',
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        $this->postJson('/api/modules/glitter-reservation_popup/admin/checkins', [
            'reservation_code' => $reservation->reservation_code,
        ])->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_filter_all_reservations(): void
    {
        $matchingEvent = $this->createEvent();
        $otherEvent = $this->createEvent();
        $matchingSlot = $this->createSlot($matchingEvent);
        $otherSlot = $this->createSlot($otherEvent);

        $keyword = 'Search-'.Str::upper(Str::random(8));

        $matching = $this->createReservation($matchingEvent, $matchingSlot, null, [
            'customer_name' => $keyword,
            'status' => 'confirmed',
            'created_at' => Carbon::parse('2026-06-12 12:00:00'),
            'updated_at' => Carbon::parse('2026-06-12 12:00:00'),
        ]);
        $this->createReservation($matchingEvent, $matchingSlot, null, [
            'customer_name' => $keyword,
            'status' => 'pending',
            'created_at' => Carbon::parse('2026-06-12 12:00:00'),
            'updated_at' => Carbon::parse('2026-06-12 12:00:00'),
        ]);
        $this->createReservation($otherEvent, $otherSlot, null, [
            'customer_name' => $keyword,
            'status' => 'confirmed',
            'created_at' => Carbon::parse('2026-06-12 12:00:00'),
            'updated_at' => Carbon::parse('2026-06-12 12:00:00'),
        ]);

        $this->getJson('/api/modules/glitter-reservation_popup/admin/reservations?'.http_build_query([
            'event_id' => $matchingEvent->id,
            'status' => 'confirmed',
            'date_from' => '2026-06-12',
            'date_to' => '2026-06-12',
            'keyword' => $keyword,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data.0.id', $matching->id)
            ->assertJsonCount(1, 'data.data');
    }

    private function createEvent(array $attributes = []): PopupEvent
    {
        return PopupEvent::query()->create(array_merge([
            'slug' => 'event-'.Str::lower(Str::random(8)),
            'title' => ['ko' => '공개 팝업'],
            'status' => 'published',
            'visibility' => 'public',
            'timezone' => 'Asia/Seoul',
            'reservation_opens_at' => Carbon::now()->subHour(),
            'reservation_closes_at' => Carbon::now()->addDays(7),
            'starts_at' => Carbon::now()->addDay(),
            'ends_at' => Carbon::now()->addDays(2),
            'capacity' => 100,
            'max_reservations_per_user' => 1,
        ], $attributes));
    }

    private function createSlot(PopupEvent $event): PopupSlot
    {
        return PopupSlot::query()->create([
            'reservation_popup_event_id' => $event->id,
            'code' => 'slot-'.Str::lower(Str::random(6)),
            'status' => 'open',
            'starts_at' => Carbon::now()->addDay()->setTime(10, 0),
            'ends_at' => Carbon::now()->addDay()->setTime(11, 0),
            'capacity' => 10,
            'reserved_count' => 0,
            'checked_in_count' => 0,
        ]);
    }

    private function createReservation(PopupEvent $event, PopupSlot $slot, ?User $user = null, array $attributes = []): PopupReservation
    {
        return PopupReservation::query()->create(array_merge([
            'reservation_popup_event_id' => $event->id,
            'reservation_popup_slot_id' => $slot->id,
            'user_id' => $user?->id,
            'reservation_code' => 'RP-'.Str::upper(Str::random(10)),
            'qr_token_hash' => hash('sha256', Str::random(32)),
            'customer_name' => '홍길동',
            'customer_phone' => '01012345678'.random_int(10, 99),
            'customer_email' => 'hong'.Str::lower(Str::random(4)).'@example.com',
            'guest_count' => 1,
            'status' => 'confirmed',
        ], $attributes));
    }
}
