<?php

namespace Modules\Glitter\ReservationPopup\Http\Controllers\Api\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Glitter\ReservationPopup\Http\Requests\StorePopupEventRequest;
use Modules\Glitter\ReservationPopup\Http\Requests\UpdatePopupEventRequest;
use Modules\Glitter\ReservationPopup\Http\Resources\PopupEventResource;
use Modules\Glitter\ReservationPopup\Services\PopupEventService;
use RuntimeException;
use Throwable;

class PopupEventController extends Controller
{
    private const MODULE = 'glitter-reservation_popup';

    public function __construct(private PopupEventService $events) {}

    public function index(Request $request): JsonResponse
    {
        try {
            return $this->ok('messages.success', PopupEventResource::collection(
                $this->events->paginate($request->only(['status', 'visibility', 'keyword']), (int) $request->integer('per_page', 15))
            ));
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function store(StorePopupEventRequest $request): JsonResponse
    {
        try {
            return $this->ok('messages.created', new PopupEventResource($this->events->createEvent($request->validated())), 201);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function show(int $event): JsonResponse
    {
        try {
            return $this->ok('messages.success', new PopupEventResource($this->events->getEvent($event)));
        } catch (RuntimeException) {
            return $this->fail('messages.events.not_found', 404);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function update(UpdatePopupEventRequest $request, int $event): JsonResponse
    {
        try {
            return $this->ok('messages.updated', new PopupEventResource($this->events->updateEvent($event, $request->validated())));
        } catch (RuntimeException) {
            return $this->fail('messages.events.not_found', 404);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function destroy(int $event): JsonResponse
    {
        try {
            $this->events->deleteEvent($event);

            return ResponseHelper::moduleSuccess(self::MODULE, 'messages.deleted');
        } catch (RuntimeException) {
            return $this->fail('messages.events.not_found', 404);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }
    private function ok(string $messageKey, mixed $resource = null, int $status = 200): JsonResponse
    {
        return ResponseHelper::moduleSuccessWithResource(self::MODULE, $messageKey, $resource, $status);
    }

    private function fail(string $messageKey, int $status = 422, mixed $errors = null): JsonResponse
    {
        return ResponseHelper::moduleError(self::MODULE, $messageKey, $status, $errors);
    }
}
