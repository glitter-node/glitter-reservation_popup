<?php

namespace Modules\Glitter\ReservationPopup\Http\Controllers\Api\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Glitter\ReservationPopup\Http\Requests\CancelPopupReservationRequest;
use Modules\Glitter\ReservationPopup\Http\Requests\StorePopupReservationRequest;
use Modules\Glitter\ReservationPopup\Http\Resources\PopupCancellationResource;
use Modules\Glitter\ReservationPopup\Http\Resources\PopupReservationResource;
use Modules\Glitter\ReservationPopup\Services\PopupCancellationService;
use Modules\Glitter\ReservationPopup\Services\PopupReservationService;
use RuntimeException;
use Throwable;

class PopupReservationController extends Controller
{
    private const MODULE = 'glitter-reservation_popup';

    public function __construct(
        private PopupReservationService $reservations,
        private PopupCancellationService $cancellations,
    ) {}

    public function store(StorePopupReservationRequest $request): JsonResponse
    {
        try {
            $payload = array_merge($request->validated(), [
                'user_id' => $request->user()?->getKey(),
                'created_by' => $request->user()?->getKey(),
                'updated_by' => $request->user()?->getKey(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);

            return $this->ok('messages.created', new PopupReservationResource($this->reservations->createReservation($payload)), 201);
        } catch (RuntimeException $e) {
            return $this->fail(str_contains($e->getMessage(), 'duplicate') ? 'messages.reservations.duplicate' : 'messages.reservations.not_available', 422);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function myReservations(Request $request): JsonResponse
    {
        try {
            return $this->ok('messages.success', PopupReservationResource::collection(
                $this->reservations->paginateForUser((int) $request->user()->getKey(), $request->only(['status']), (int) $request->integer('per_page', 15))
            ));
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function show(Request $request, string $code): JsonResponse
    {
        try {
            return $this->ok('messages.success', new PopupReservationResource(
                $this->reservations->getReservationForUser($code, (int) $request->user()->getKey())
            ));
        } catch (RuntimeException) {
            return $this->fail('messages.reservations.not_found', 404);
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function cancel(CancelPopupReservationRequest $request, string $code): JsonResponse
    {
        try {
            $payload = array_merge($request->validated(), [
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);

            return $this->ok('messages.updated', new PopupCancellationResource(
                $this->cancellations->cancelByReservationCodeForUser($code, (int) $request->user()->getKey(), $payload)
            ));
        } catch (RuntimeException $e) {
            return $this->fail(str_contains($e->getMessage(), 'not_found') ? 'messages.reservations.not_found' : 'messages.cancellations.not_allowed', 422);
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
