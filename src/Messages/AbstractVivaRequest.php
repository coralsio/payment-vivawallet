<?php

namespace Corals\Modules\Payment\Vivawallet\Messages;

use Corals\Modules\Payment\Common\Message\AbstractRequest;
use Corals\Modules\Payment\Vivawallet\Classes\Client;
use GuzzleHttp\Client as HttpClient;

abstract class AbstractVivaRequest extends AbstractRequest
{
    public function getClient()
    {
        $guzzle_client = new HttpClient([
            'curl' => $this->curlDoesntUseNss()
                ? [CURLOPT_SSL_CIPHER_LIST => 'TLSv1.2']
                : [],
        ]);

        return new Client(
            $guzzle_client);
    }

    /**
     * Check if cURL doens't use NSS.
     *
     * @return bool
     */
    protected function curlDoesntUseNss()
    {
        $curl = curl_version();

        return !preg_match('/NSS/', $curl['ssl_version']);
    }

    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }
}
