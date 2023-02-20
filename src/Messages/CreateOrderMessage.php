<?php

namespace Corals\Modules\Payment\Vivawallet\Messages;

use Corals\Modules\Payment\Vivawallet\Classes\Order;

class CreateOrderMessage extends AbstractVivaRequest
{

    public function setDetails($details)
    {
        $this->setParameter('details', $details);
    }

    public function getDetails()
    {
        return $this->getParameter('details');
    }

    public function getData()
    {
        return [
            'amount' => $this->getAmountInteger(),
            'details' => $this->getDetails(),
        ];
    }

    public function sendData($data)
    {
        $viva_order = new Order($this->getClient());

        $orderCode = $viva_order->create(data_get($data, 'amount'), data_get($data, 'details'));

        return $viva_order->getCheckoutUrl($orderCode);
    }
}
