<?php

namespace Corals\Modules\Payment\Vivawallet\Classes;

use Corals\Foundation\Http\Controllers\PublicBaseController;
use Corals\Modules\Payment\Payment;
use Corals\Settings\Facades\Modules;
use Illuminate\Http\Request;

abstract class WebhookController extends PublicBaseController
{
    /**
     * @var \Corals\Modules\Payment\Vivawallet\Classes\Webhook
     */
    protected $webhook;

    protected $gateway;
    protected $namespace;

    public function __construct(Webhook $webhook)
    {
        $this->webhook = $webhook;

        parent::__construct();
    }

    protected function initController()
    {
        $gateway = Payment::create('Vivawallet');

        $gateway->setAuthentication();

        $this->gateway = $gateway;

        if (Modules::isModuleActive('corals-marketplace')) {
            $this->namespace = "Marketplace";
        } else {
            $this->namespace = "Ecommerce";
        }
    }

    /**
     * Handle an incoming request.
     *
     * Handle a GET verification request or a POST notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        $this->initController();

        if ($request->method() == 'GET') {
            return $this->verify();
        }

        return $this->handleTransaction($request);
    }

    /**
     * Handle a POST notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    protected function handleTransaction(Request $request)
    {
        switch ($request->get('EventTypeId')) {
            case Webhook::CREATE_TRANSACTION:
                return $this->handleCreateTransaction($request);
            case Webhook::REFUND_TRANSACTION:
                return $this->handleRefundTransaction($request);
            default:
                return $this->handleEventNotification($request);
        }
    }

    /**
     * Handle a Create Transaction event notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    abstract protected function handleCreateTransaction(Request $request);

    /**
     * Handle a Refund Transaction event notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    abstract protected function handleRefundTransaction(Request $request);

    /**
     * Handle any other type of event notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    abstract protected function handleEventNotification(Request $request);

    /**
     * Verify a webhook.
     */
    protected function verify(): array
    {
        return (array)$this->webhook->getAuthorizationCode();
    }
}
