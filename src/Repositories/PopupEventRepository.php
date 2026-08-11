<?php

namespace Modules\Glitter\ReservationPopup\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\ReservationPopup\Contracts\PopupEventRepositoryInterface;
use Modules\Glitter\ReservationPopup\Models\PopupEvent;

class PopupEventRepository implements PopupEventRepositoryInterface
{
    public function create(array $data): PopupEvent
    {
        $model = PopupEvent::query()->create($data);

        return $model->fresh(['slots']) ?? $model;
    }

    public function update(PopupEvent $event, array $data): PopupEvent
    {
        $event->fill($data);
        $event->save();

        return $event->fresh(['slots']) ?? $event;
    }

    public function delete(PopupEvent $event): bool
    {
        return (bool) $event->delete();
    }

    public function find(int $id): ?PopupEvent
    {
        return PopupEvent::query()->find($id);
    }

    public function findWithCounts(int $id): ?PopupEvent
    {
        return PopupEvent::query()
            ->withCount(['slots as slot_count', 'reservations as reservation_count'])
            ->find($id);
    }

    public function findBySlug(string $slug): ?PopupEvent
    {
        return PopupEvent::query()->where('slug', $slug)->first();
    }

    public function findPublicBySlug(string $slug): ?PopupEvent
    {
        return PopupEvent::query()
            ->with('slots')
            ->withCount(['slots as slot_count', 'reservations as reservation_count'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PopupEvent::query()
            ->withCount(['slots as slot_count', 'reservations as reservation_count'])
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = (string) $filters['keyword'];
            $query->where(function ($inner) use ($keyword): void {
                $inner->where('slug', 'like', '%'.$keyword.'%')
                    ->orWhere('title', 'like', '%'.$keyword.'%');
            });
        }

        return $query->paginate(max($perPage, 1));
    }

    public function paginatePublic(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PopupEvent::query()
            ->withCount(['slots as slot_count', 'reservations as reservation_count'])
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->orderBy('starts_at')
            ->orderByDesc('created_at');

        if (! empty($filters['keyword'])) {
            $keyword = (string) $filters['keyword'];
            $query->where(function ($inner) use ($keyword): void {
                $inner->where('slug', 'like', '%'.$keyword.'%')
                    ->orWhere('title', 'like', '%'.$keyword.'%');
            });
        }

        return $query->paginate(max($perPage, 1));
    }
}
