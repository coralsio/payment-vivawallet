<?php

namespace Corals\Modules\Payment\Vivawallet\Messages;

use Corals\Modules\Payment\Vivawallet\Classes\OAuth;
use GuzzleHttp\RequestOptions;

class ObligationMessage extends AbstractVivaRequest
{
    public function setPersonId($value)
    {
        $this->setParameter('personId', $value);
    }

    public function getPersonId()
    {
        return $this->getParameter('personId');
    }

    public function setWalletId($value)
    {
        $this->setParameter('walletId', $value);
    }

    public function getWalletId()
    {
        return $this->getParameter('walletId');
    }

    public function getData()
    {
        return [
            'personId' => $this->getPersonId(),
            'walletId' => $this->getWalletId(),
            'amount' => $this->getAmountInteger(),
            'descriptions' => $this->getDescription(),
            'currencyCode' => '826',
        ];
    }

    public function sendData($data)
    {
        $client = $this->getClient();
        $oauth = app()->make(OAuth::class);

        $clientId = $client->getGateway()->getSettings(($client->isSandbox() ? 'sandbox_' : 'live_') . 'client_id');
        $clientSecret = $client->getGateway()->getSettings(($client->isSandbox() ? 'sandbox_' : 'live_') . 'client_secret');

        $token = $oauth->requestToken($clientId, $clientSecret);

        $client->withToken($token->access_token);

        $response = (array)$client->post(
            $client->getApiUrl()->withPath("/obligations/v1/obligations"),
            array_merge_recursive(
                [RequestOptions::JSON => $data],
                $client->authenticateWithBearerToken(), []
            )
        );

        dd('response', $response);
        return $this->createResponse(['id' => $response, 'StatusId' => 'F']);
    }
}
