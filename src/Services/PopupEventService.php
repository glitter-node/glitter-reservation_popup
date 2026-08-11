<?php

namespace Modules\Glitter\ReservationPopup\Services;

use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;
use Modules\Glitter\ReservationPopup\Contracts\PopupEventRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupEvent;

class PopupEventService
{
    public function __construct(
        private PopupEventRepositoryInterface $events,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->events->paginate($filters, $perPage);
    }

    public function paginatePublic(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->events->paginatePublic($filters, $perPage);
    }

    public function createEvent(array $data): PopupEvent
    {
        return $this->events->create($this->normalizeEventPayload($data));
    }

    public function updateEvent(int $eventId, array $data): PopupEvent
    {
        $event = $this->getEvent($eventId);

        return $this->events->update($event, $this->normalizeEventPayload($data));
    }

    public function deleteEvent(int $eventId): bool
    {
        return $this->events->delete($this->getEvent($eventId));
    }

    public function getEvent(int $eventId): PopupEvent
    {
        $event = $this->events->findWithCounts($eventId) ?? $this->events->find($eventId);

        if ($event === null) {
            throw new RuntimeException($this->message('events.not_found'));
        }

        return $event;
    }

    public function getPublicEventBySlug(string $slug): PopupEvent
    {
        $event = $this->events->findPublicBySlug($slug);

        if ($event === null) {
            throw new RuntimeException($this->message('events.not_found'));
        }

        return $event;
    }

    private function normalizeEventPayload(array $data): array
    {
        if (empty($data['timezone'])) {
            $data['timezone'] = $this->defaultTimezone();
        }

        if (! array_key_exists('max_reservations_per_user', $data) || $data['max_reservations_per_user'] === null) {
            $data['max_reservations_per_user'] = 1;
        }

        return $data;
    }

    private function defaultTimezone(): string
    {
        if (Container::getInstance()->bound('config')) {
            return (string) config('app.timezone', 'Asia/Seoul');
        }

        return 'Asia/Seoul';
    }

    private function message(string $key): string
    {
        $translationKey = "glitter-reservation_popup::messages.{$key}";

        if (Container::getInstance()->bound('translator')) {
            return __($translationKey);
        }

        return $translationKey;
    }
}
