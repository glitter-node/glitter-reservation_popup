<?php

namespace Modules\Glitter\ReservationPopup\Providers;

use App\Extension\BaseModuleServiceProvider;
use Modules\Glitter\ReservationPopup\Contracts\PopupCancellationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupCheckinRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupEventRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupReservationRepositoryInterface;
use Modules\Glitter\ReservationPopup\Contracts\PopupSlotRepositoryInterface;
use Modules\Glitter\ReservationPopup\Repositories\PopupCancellationRepository;
use Modules\Glitter\ReservationPopup\Repositories\PopupCheckinRepository;
use Modules\Glitter\ReservationPopup\Repositories\PopupEventRepository;
use Modules\Glitter\ReservationPopup\Repositories\PopupReservationRepository;
use Modules\Glitter\ReservationPopup\Repositories\PopupSlotRepository;

class ReservationPopupServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleIdentifier = 'glitter-reservation_popup';

    protected array $repositories = [
        PopupEventRepositoryInterface::class => PopupEventRepository::class,
        PopupSlotRepositoryInterface::class => PopupSlotRepository::class,
        PopupReservationRepositoryInterface::class => PopupReservationRepository::class,
        PopupCancellationRepositoryInterface::class => PopupCancellationRepository::class,
        PopupCheckinRepositoryInterface::class => PopupCheckinRepository::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/reservation_popup.php', 'reservation_popup');
    }

    protected function loadModuleTranslations(): void
    {
        $langPath = dirname(__DIR__, 2).'/resources/lang';

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleIdentifier);
            $this->loadJsonTranslationsFrom($langPath);
        }
    }
}
