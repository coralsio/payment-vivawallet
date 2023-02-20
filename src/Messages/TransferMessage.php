<?php

namespace Corals\Modules\Payment\Vivawallet\Messages;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;

class TransferMessage extends AbstractVivaRequest
{
    public function setSourceTransaction($value)
    {
        $this->setParameter('transactionId', $value);
    }

    public function setWalletId($value)
    {
        $this->setParameter('walletId', $value);
    }

    public function getWalletId()
    {
        return $this->getParameter('walletId');
    }

    public function setDestination($value)
    {
        $this->setParameter('targetWalletId', $value);
    }

    public function getTargetWalletId()
    {
        return $this->getParameter('targetWalletId');
    }

    public function getData()
    {
        return [
            'walletId' => $this->getWalletId(),
            'targetWalletId' => $this->getTargetWalletId(),
            'amount' => $this->getAmountInteger(),
            'saleTransactionId' => $this->getTransactionId(),
        ];
    }

    public function sendData($data)
    {
        $client = $this->getClient();

        $parameters = Arr::only($data, ['amount', 'saleTransactionId']);

        $walletId = data_get($data, 'walletId');

        $targetWalletId = data_get($data, 'targetWalletId');

        $response = (array)$client->post(
            $client->getUrl()->withPath("/api/wallets/{$walletId}/balancetransfer/{$targetWalletId}"),
            array_merge_recursive(
                [RequestOptions::JSON => $parameters],
                $client->authenticateWithBasicAuth(), []
            )
        );

        return $this->createResponse(['id' => $response, 'StatusId' => 'F']);
    }
}
