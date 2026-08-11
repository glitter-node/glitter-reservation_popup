<?php

namespace Modules\Glitter\ReservationPopup\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\Glitter\ReservationPopup\Providers\ReservationPopupServiceProvider;
use Tests\TestCase;

abstract class ModuleFeatureTestCase extends TestCase
{
    use DatabaseTransactions;

    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(ReservationPopupServiceProvider::class);
        $this->withoutMiddleware();
        $this->runModuleMigrationIfNeeded();
        $this->registerModuleRoutes();
    }

    protected function runModuleMigrationIfNeeded(): void
    {
        if (static::$migrated) {
            return;
        }

        if (! Schema::hasTable('users')) {
            $this->artisan('migrate');
        }

        if (! Schema::hasTable('reservation_popup_events')) {
            $this->artisan('migrate', [
                '--path' => dirname(__DIR__).'/database/migrations',
                '--realpath' => true,
            ]);
        }

        static::$migrated = true;
    }

    protected function registerModuleRoutes(): void
    {
        if (Route::getRoutes()->getByName('api.modules.glitter-reservation_popup.admin.events.index')) {
            return;
        }

        Route::middleware('api')
            ->prefix('api/modules/glitter-reservation_popup')
            ->name('api.modules.glitter-reservation_popup.')
            ->group(dirname(__DIR__).'/src/routes/api.php');
    }
}
