<?php

namespace Corals\Modules\Payment\Vivawallet\Providers;

use Corals\Foundation\Providers\BaseUpdateModuleServiceProvider;

class UpdateModuleServiceProvider extends BaseUpdateModuleServiceProvider
{
    protected $module_code = 'corals-payment-vivawallet';
    protected $batches_path = __DIR__ . '/../update-batches/*.php';
}
