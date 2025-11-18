<?php

namespace App\Providers;

use App\Repositories\BatteryRepository;
use App\Repositories\BatteryRepositoryInterface;
use App\Repositories\ConnectorRepository;
use App\Repositories\ConnectorRepositoryInterface;
use App\Repositories\SolarPanelRepository;
use App\Repositories\SolarPanelRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interfaces to concrete implementations
        $this->app->bind(BatteryRepositoryInterface::class, BatteryRepository::class);
        $this->app->bind(ConnectorRepositoryInterface::class, ConnectorRepository::class);
        $this->app->bind(SolarPanelRepositoryInterface::class, SolarPanelRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
