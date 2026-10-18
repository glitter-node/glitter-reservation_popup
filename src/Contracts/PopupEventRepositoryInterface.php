<?php

namespace Modules\Glitter\ReservationPopup\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\ReservationPopup\Models\PopupEvent;

interface PopupEventRepositoryInterface
{
    public function create(array $data): PopupEvent;

    public function update(PopupEvent $event, array $data): PopupEvent;

    public function delete(PopupEvent $event): bool;

    public function find(int $id): ?PopupEvent;

    public function findWithCounts(int $id): ?PopupEvent;

    public function findBySlug(string $slug): ?PopupEvent;

    public function findPublicBySlug(string $slug): ?PopupEvent;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginatePublic(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
