<?php

namespace Modules\Glitter\ReservationPopup\Http\Controllers\Api\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Glitter\ReservationPopup\Http\Requests\UpdatePopupSlotRequest;
use Modules\Glitter\ReservationPopup\Http\Requests\GeneratePopupSlotsRequest;
use Modules\Glitter\ReservationPopup\Http\Resources\PopupSlotResource;
use Modules\Glitter\ReservationPopup\Services\PopupSlotService;
use RuntimeException;
use Throwable;

class PopupSlotController extends Controller
{
    private const MODULE = 'glitter-reservation_popup';

    public function __construct(private PopupSlotService $slots) {}

    public function index(Request $request, int $event): JsonResponse
    {
        try {
            return $this->ok('messages.success', PopupSlotResource::collection(
                $this->slots->paginateForEvent($event, (int) $request->integer('per_page', 50))
            ));
        } catch (RuntimeException) {
            return $this->fail('messages.events.not_found', 404);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function generate(GeneratePopupSlotsRequest $request, int $event): JsonResponse
    {
        try {
            return $this->ok('messages.created', PopupSlotResource::collection(
                collect($this->slots->generateSlots($event, $request->validated('slots')))
            ), 201);
        } catch (RuntimeException $e) {
            return $this->fail(str_contains($e->getMessage(), 'events.not_found') ? 'messages.events.not_found' : 'messages.errors.unprocessable', 422);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function update(UpdatePopupSlotRequest $request, int $event, int $slot): JsonResponse
    {
        try {
            return $this->ok('messages.updated', new PopupSlotResource($this->slots->updateSlot($event, $slot, $request->validated())));
        } catch (RuntimeException) {
            return $this->fail('messages.slots.not_found', 404);
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
