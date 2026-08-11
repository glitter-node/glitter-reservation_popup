<?php

namespace Modules\Glitter\ReservationPopup\Tests\Unit;


spl_autoload_register(static function (string $class): void {
    $prefix = 'Modules\\Glitter\\ReservationPopup\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = dirname(__DIR__, 2).'/src/'.str_replace('\\', '/', $relative).'.php';

    if (is_file($file)) {
        require_once $file;
    }
});

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Modules\Glitter\ReservationPopup\Contracts\PopupCancellationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupCheckinRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupEventRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupReservationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupSlotRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupCancellation;
use Modules\Glitter\ReservationPopup\Models\PopupCheckin;
use Modules\Glitter\ReservationPopup\Models\PopupEvent;
use Modules\Glitter\ReservationPopup\Models\PopupReservation;
use Modules\Glitter\ReservationPopup\Models\PopupSlot;
use Modules\Glitter\ReservationPopup\Services\PopupCancellationService;
use Modules\Glitter\ReservationPopup\Services\PopupCheckinService;
use Modules\Glitter\ReservationPopup\Services\PopupEventService;
use Modules\Glitter\ReservationPopup\Services\PopupReservationService;
use Modules\Glitter\ReservationPopup\Services\PopupSlotService;
use RuntimeException;
use PHPUnit\Framework\TestCase;

class DomainServicesTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_event_service_creates_event_through_repository(): void
    {
        $events = new FakeEventRepository();
        $service = new PopupEventService($events);

        $event = $service->createEvent([
            'slug' => 'spring-popup',
            'title' => ['ko' => '봄 팝업'],
            'status' => 'draft',
            'visibility' => 'public',
        ]);

        $this->assertSame('spring-popup', $event->slug);
        $this->assertSame(['ko' => '봄 팝업'], $events->createdPayload['title']);
    }

    public function test_slot_service_creates_slot_for_existing_event(): void
    {
        $event = $this->event(['id' => 10]);
        $events = new FakeEventRepository(findResult: $event);
        $slots = new FakeSlotRepository();
        $service = new PopupSlotService($slots, $events, new FakeReservationRepository());

        $slot = $service->createSlot(10, [
            'code' => 'am-1',
            'status' => 'open',
            'capacity' => 20,
        ]);

        $this->assertSame(10, $slot->reservation_popup_event_id);
        $this->assertSame('am-1', $slots->createdPayload['code']);
    }

    public function test_reservation_service_judges_reservation_availability(): void
    {
        Carbon::setTestNow('2026-06-13 10:00:00');

        $event = $this->event([
            'id' => 1,
            'status' => 'published',
            'visibility' => 'public',
            'timezone' => 'Asia/Seoul',
            'capacity' => 10,
            'reservation_opens_at' => Carbon::parse('2026-06-13 09:00:00'),
            'reservation_closes_at' => Carbon::parse('2026-06-13 18:00:00'),
        ]);
        $slot = $this->slot([
            'id' => 2,
            'reservation_popup_event_id' => 1,
            'status' => 'open',
            'capacity' => 5,
        ]);
        $reservations = new FakeReservationRepository(activeEventCount: 4, activeSlotCount: 3);
        $service = new PopupReservationService($reservations, new FakeEventRepository(), new FakeSlotRepository());

        $this->assertTrue($service->canReserve($event, $slot, 2));
        $this->assertFalse($service->canReserve($event, $slot, 3));
    }

    public function test_cancellation_service_judges_cancellation_availability(): void
    {
        $reservation = $this->reservation(['id' => 30, 'status' => 'confirmed']);
        $service = new PopupCancellationService(
            new FakeCancellationRepository(),
            new FakeReservationRepository(),
            new FakeSlotRepository(),
        );

        $this->assertTrue($service->canCancel($reservation));

        $cancelled = $this->reservation(['id' => 31, 'status' => 'cancelled']);
        $this->assertFalse($service->canCancel($cancelled));
    }

    public function test_checkin_service_blocks_duplicate_qr_checkin(): void
    {
        $reservation = $this->reservation(['id' => 40, 'status' => 'confirmed']);
        $service = new PopupCheckinService(
            new FakeCheckinRepository(exists: true),
            new FakeReservationRepository(),
            new FakeSlotRepository(),
        );

        $this->expectException(RuntimeException::class);

        $service->checkInReservation($reservation);
    }

    private function event(array $attributes = []): PopupEvent
    {
        $event = new PopupEvent();
        $event->setRawAttributes(array_merge([
            'id' => $attributes['id'] ?? 1,
            'slug' => 'event',
            'title' => ['ko' => '이벤트'],
            'status' => 'published',
            'visibility' => 'public',
            'timezone' => 'Asia/Seoul',
        ], $attributes), true);
        $event->exists = true;

        return $event;
    }

    private function slot(array $attributes = []): PopupSlot
    {
        $slot = new PopupSlot();
        $slot->setRawAttributes(array_merge([
            'id' => $attributes['id'] ?? 1,
            'reservation_popup_event_id' => 1,
            'code' => 'slot',
            'status' => 'open',
            'capacity' => 10,
        ], $attributes), true);
        $slot->exists = true;

        return $slot;
    }

    private function reservation(array $attributes = []): PopupReservation
    {
        $reservation = new PopupReservation();
        $reservation->setRawAttributes(array_merge([
            'id' => $attributes['id'] ?? 1,
            'reservation_popup_event_id' => 1,
            'reservation_popup_slot_id' => null,
            'reservation_code' => 'RP-TEST',
            'qr_token_hash' => hash('sha256', 'token'),
            'customer_name' => 'Tester',
            'customer_phone' => '01000000000',
            'guest_count' => 1,
            'status' => 'confirmed',
        ], $attributes), true);
        $reservation->exists = true;

        return $reservation;
    }
}

final class FakeEventRepository implements PopupEventRepositoryInterface
{
    public array $createdPayload = [];

    public function __construct(private ?PopupEvent $findResult = null) {}

    public function create(array $data): PopupEvent
    {
        $this->createdPayload = $data;
        $event = new PopupEvent();
        $event->setRawAttributes(array_merge(['id' => 1], $data), true);
        $event->exists = true;

        return $event;
    }

    public function update(PopupEvent $event, array $data): PopupEvent
    {
        $event->fill($data);

        return $event;
    }

    public function delete(PopupEvent $event): bool
    {
        return true;
    }

    public function find(int $id): ?PopupEvent
    {
        return $this->findResult;
    }

    public function findWithCounts(int $id): ?PopupEvent
    {
        return $this->findResult;
    }

    public function findBySlug(string $slug): ?PopupEvent
    {
        return $this->findResult;
    }

    public function findPublicBySlug(string $slug): ?PopupEvent
    {
        return $this->findResult;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        throw new RuntimeException('Not used by this test.');
    }

    public function paginatePublic(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        throw new RuntimeException('Not used by this test.');
    }
}

final class FakeSlotRepository implements PopupSlotRepositoryInterface
{
    public array $createdPayload = [];
    public int $reservedIncrement = 0;
    public int $reservedDecrement = 0;
    public int $checkedInIncrement = 0;

    public function __construct(private ?PopupSlot $findResult = null) {}

    public function create(array $data): PopupSlot
    {
        $this->createdPayload = $data;
        $slot = new PopupSlot();
        $slot->setRawAttributes(array_merge(['id' => 1], $data), true);
        $slot->exists = true;

        return $slot;
    }

    public function update(PopupSlot $slot, array $data): PopupSlot
    {
        $slot->fill($data);

        return $slot;
    }

    public function delete(PopupSlot $slot): bool
    {
        return true;
    }

    public function find(int $id): ?PopupSlot
    {
        return $this->findResult;
    }

    public function findForEvent(int $eventId, int $slotId): ?PopupSlot
    {
        return $this->findResult;
    }

    public function listForEvent(int $eventId): Collection
    {
        return new Collection();
    }

    public function paginateForEvent(int $eventId, int $perPage = 15): LengthAwarePaginator
    {
        throw new RuntimeException('Not used by this test.');
    }

    public function incrementReservedCount(PopupSlot $slot, int $amount): void
    {
        $this->reservedIncrement += $amount;
    }

    public function decrementReservedCount(PopupSlot $slot, int $amount): void
    {
        $this->reservedDecrement += $amount;
    }

    public function incrementCheckedInCount(PopupSlot $slot, int $amount): void
    {
        $this->checkedInIncrement += $amount;
    }
}

final class FakeReservationRepository implements PopupReservationRepositoryInterface
{
    public function __construct(
        private int $activeEventCount = 0,
        private int $activeSlotCount = 0,
        private bool $duplicateCustomer = false,
        private ?PopupReservation $reservation = null,
    ) {}

    public function create(array $data): PopupReservation
    {
        $reservation = new PopupReservation();
        $reservation->setRawAttributes(array_merge(['id' => 1], $data), true);
        $reservation->exists = true;

        return $reservation;
    }

    public function update(PopupReservation $reservation, array $data): PopupReservation
    {
        $reservation->fill($data);

        return $reservation;
    }

    public function find(int $id): ?PopupReservation
    {
        return $this->reservation;
    }

    public function findByCode(string $code): ?PopupReservation
    {
        return $this->reservation;
    }

    public function findByCodeForUser(string $code, int $userId): ?PopupReservation
    {
        return $this->reservation;
    }

    public function findByQrTokenHash(string $hash): ?PopupReservation
    {
        return $this->reservation;
    }

    public function existsActiveForCustomer(int $eventId, ?int $userId, string $phone, ?string $email): bool
    {
        return $this->duplicateCustomer;
    }

    public function countActiveForSlot(int $slotId): int
    {
        return $this->activeSlotCount;
    }

    public function countActiveForEvent(int $eventId): int
    {
        return $this->activeEventCount;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        throw new RuntimeException('Not used by this test.');
    }

    public function paginateForEvent(int $eventId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        throw new RuntimeException('Not used by this test.');
    }

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        throw new RuntimeException('Not used by this test.');
    }
}

final class FakeCancellationRepository implements PopupCancellationRepositoryInterface
{
    public function __construct(private bool $exists = false) {}

    public function create(array $data): PopupCancellation
    {
        $cancellation = new PopupCancellation();
        $cancellation->setRawAttributes(array_merge(['id' => 1], $data), true);
        $cancellation->exists = true;

        return $cancellation;
    }

    public function existsForReservation(int $reservationId): bool
    {
        return $this->exists;
    }
}

final class FakeCheckinRepository implements PopupCheckinRepositoryInterface
{
    public function __construct(private bool $exists = false) {}

    public function create(array $data): PopupCheckin
    {
        $checkin = new PopupCheckin();
        $checkin->setRawAttributes(array_merge(['id' => 1], $data), true);
        $checkin->exists = true;

        return $checkin;
    }

    public function existsForReservation(int $reservationId): bool
    {
        return $this->exists;
    }

    public function findByReservationId(int $reservationId): ?PopupCheckin
    {
        return null;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        throw new RuntimeException('Not used by this test.');
    }
}
