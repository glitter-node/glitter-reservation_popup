<?php

namespace Modules\Glitter\ReservationPopup\Http\Controllers\Api\Public;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Glitter\ReservationPopup\Http\Resources\PopupEventResource;
use Modules\Glitter\ReservationPopup\Http\Resources\PopupSlotResource;
use Modules\Glitter\ReservationPopup\Services\PopupEventService;
use Modules\Glitter\ReservationPopup\Services\PopupSlotService;
use RuntimeException;
use Throwable;

class PopupEventController extends Controller
{
    private const MODULE = 'glitter-reservation_popup';

    public function __construct(
        private PopupEventService $events,
        private PopupSlotService $slots,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            return $this->ok('messages.success', PopupEventResource::collection(
                $this->events->paginatePublic($request->only(['keyword']), (int) $request->integer('per_page', 15))
            ));
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function show(string $slug): JsonResponse
    {
        try {
            return $this->ok('messages.success', new PopupEventResource($this->events->getPublicEventBySlug($slug)));
        } catch (RuntimeException) {
            return $this->fail('messages.events.not_found', 404);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function slots(string $slug): JsonResponse
    {
        try {
            $event = $this->events->getPublicEventBySlug($slug);

            return $this->ok('messages.success', PopupSlotResource::collection($this->slots->listForEvent((int) $event->getKey())));
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
