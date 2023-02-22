<?php

namespace Corals\Modules\Payment\Vivawallet;

use Corals\Foundation\Providers\BasePackageServiceProvider;
use Corals\Modules\Payment\Vivawallet\Providers\VivawalletRouteServiceProvider;
use Corals\Settings\Facades\Modules;

class VivawalletServiceProvider extends BasePackageServiceProvider
{
    /**
     * @var
     */
    protected $defer = false;
    /**
     * @var
     */
    protected $packageCode = 'corals-payment-vivawallet';
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function bootPackage()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function registerPackage()
    {
        $this->app->register(VivawalletRouteServiceProvider::class);
    }

    public function registerModulesPackages()
    {
        Modules::addModulesPackages('corals/payment-vivawallet');
    }
}
