<?php

namespace Corals\Modules\Payment\Vivawallet\Messages;

use Corals\Modules\Payment\Vivawallet\Classes\Transaction;

class RefundTransactionMessage extends AbstractVivaRequest
{

    public function getData()
    {
        return [
            'amount' => $this->getAmountInteger(),
            'id' => $this->getTransactionId(),
        ];
    }

    public function sendData($data)
    {
        $transaction = new Transaction($this->getClient());

        $response =(array) $transaction->cancel(data_get($data, 'id'), data_get($data, 'amount'));

        return $this->createResponse($response);
    }
}
