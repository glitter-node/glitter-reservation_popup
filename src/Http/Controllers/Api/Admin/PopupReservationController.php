<?php

namespace Modules\Glitter\ReservationPopup\Http\Controllers\Api\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Glitter\ReservationPopup\Http\Requests\IndexAdminPopupReservationsRequest;
use Modules\Glitter\ReservationPopup\Http\Requests\UpdatePopupReservationStatusRequest;
use Modules\Glitter\ReservationPopup\Http\Resources\PopupReservationResource;
use Modules\Glitter\ReservationPopup\Services\PopupReservationService;
use RuntimeException;
use Throwable;

class PopupReservationController extends Controller
{
    private const MODULE = 'glitter-reservation_popup';

    public function __construct(private PopupReservationService $reservations) {}

    public function index(IndexAdminPopupReservationsRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $perPage = (int) ($filters['per_page'] ?? 15);
            unset($filters['per_page']);

            return $this->ok('messages.success', PopupReservationResource::collection(
                $this->reservations->paginate($filters, $perPage)
            ));
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function eventIndex(Request $request, int $event): JsonResponse
    {
        try {
            return $this->ok('messages.success', PopupReservationResource::collection(
                $this->reservations->paginateForEvent($event, $request->only(['status', 'slot_id', 'customer_phone']), (int) $request->integer('per_page', 15))
            ));
        } catch (RuntimeException) {
            return $this->fail('messages.events.not_found', 404);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function show(int $reservation): JsonResponse
    {
        try {
            return $this->ok('messages.success', new PopupReservationResource($this->reservations->getReservation($reservation)));
        } catch (RuntimeException) {
            return $this->fail('messages.reservations.not_found', 404);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function updateStatus(UpdatePopupReservationStatusRequest $request, int $reservation): JsonResponse
    {
        try {
            return $this->ok('messages.updated', new PopupReservationResource(
                $this->reservations->updateStatus(
                    $reservation,
                    (string) $request->validated('status'),
                    $request->validated('admin_memo'),
                    $request->user()?->getKey(),
                )
            ));
        } catch (RuntimeException $e) {
            return $this->fail(str_contains($e->getMessage(), 'not_found') ? 'messages.reservations.not_found' : 'messages.errors.unprocessable', 422);
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
