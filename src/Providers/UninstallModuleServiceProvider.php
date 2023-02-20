<?php

namespace Corals\Modules\Payment\Vivawallet\Providers;

use Corals\Foundation\Providers\BaseUninstallModuleServiceProvider;
use Corals\Settings\Models\Setting;
use Corals\User\Models\User;

class UninstallModuleServiceProvider extends BaseUninstallModuleServiceProvider
{
    protected function providerBooted()
    {
        $supported_gateways = \Settings::get('supported_payment_gateway', []);

        if (is_array($supported_gateways)) {
            unset($supported_gateways['Vivawallet']);
        }

        \Settings::set('supported_payment_gateway', json_encode($supported_gateways));

        Setting::where('code', 'like', 'payment_vivawallet%')->delete();

        User::where('gateway', 'Vivawallet')->update(['gateway' => null]);
    }
}
