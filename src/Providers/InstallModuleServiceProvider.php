<?php

namespace Corals\Modules\Payment\Vivawallet\Providers;

use Corals\Foundation\Providers\BaseInstallModuleServiceProvider;

class InstallModuleServiceProvider extends BaseInstallModuleServiceProvider
{
    protected function providerBooted()
    {
        $supported_gateways = \Payments::getAvailableGateways();

        $supported_gateways['Vivawallet'] = 'Vivawallet';

        \Payments::setAvailableGateways($supported_gateways);
    }
}
