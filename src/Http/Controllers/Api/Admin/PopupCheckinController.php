<?php

namespace Modules\Glitter\ReservationPopup\Http\Controllers\Api\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Glitter\ReservationPopup\Http\Requests\StorePopupCheckinRequest;
use Modules\Glitter\ReservationPopup\Http\Resources\PopupCheckinResource;
use Modules\Glitter\ReservationPopup\Services\PopupCheckinService;
use RuntimeException;
use Throwable;

class PopupCheckinController extends Controller
{
    private const MODULE = 'glitter-reservation_popup';

    public function __construct(private PopupCheckinService $checkins) {}

    public function index(Request $request): JsonResponse
    {
        try {
            return $this->ok('messages.success', PopupCheckinResource::collection(
                $this->checkins->paginate($request->only(['event_id', 'slot_id', 'status']), (int) $request->integer('per_page', 15))
            ));
        } catch (Throwable $e) {
            return $this->fail('messages.errors.server', 500, $e);
        }
    }

    public function store(StorePopupCheckinRequest $request): JsonResponse
    {
        try {
            $payload = $request->validated();
            $payload['checked_in_by'] = $request->user()?->getKey();
            $payload['ip_address'] = $request->ip();
            $payload['user_agent'] = (string) $request->userAgent();

            $checkin = isset($payload['qr_token'])
                ? $this->checkins->checkInByQrToken((string) $payload['qr_token'], $payload)
                : $this->checkins->checkInByReservationCode((string) $payload['reservation_code'], $payload);

            return $this->ok('messages.created', new PopupCheckinResource($checkin), 201);
        } catch (RuntimeException $e) {
            return $this->fail(str_contains($e->getMessage(), 'duplicate') ? 'messages.checkins.duplicate' : 'messages.errors.unprocessable', 422);
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
