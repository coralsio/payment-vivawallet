<?php

namespace Corals\Modules\Payment\Vivawallet;

use Corals\Modules\Payment\Vivawallet\Providers\VivawalletRouteServiceProvider;
use Illuminate\Support\ServiceProvider;
use Corals\Settings\Facades\Modules;

class VivawalletServiceProvider extends ServiceProvider
{
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerModulesPackages();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(VivawalletRouteServiceProvider::class);
    }

    public function registerModulesPackages()
    {
        Modules::addModulesPackages('corals/payment-vivawallet');
    }
}
