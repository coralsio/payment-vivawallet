<?php

namespace Corals\Modules\Payment\Vivawallet\Exception;

use Corals\Modules\Payment\Common\Exception\WebhookFailed;
use Corals\Modules\Payment\Common\Models\WebhookCall;

class VivawalletWebhookFailed extends WebhookFailed
{

    public static function invalidVivawalletPayload(WebhookCall $webhookCall)
    {
        return new static(trans('Vivawallet::exception.invalid_vivawallet_payload', ['arg' => $webhookCall->id]));
    }

    public static function invalidSuccessStatus(WebhookCall $webhookCall)
    {
        return new static(trans('Vivawallet::exception.invalid_success_status', ['arg' => $webhookCall->id]));
    }
}
