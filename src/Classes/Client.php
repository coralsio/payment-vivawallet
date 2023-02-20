<?php

namespace Corals\Modules\Payment\Vivawallet\Classes;

use Corals\Modules\Payment\Payment;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Client
{
    /**
     * Demo environment URL.
     */
    const DEMO_URL = 'https://demo.vivapayments.com';

    /**
     * Production environment URL.
     */
    const PRODUCTION_URL = 'https://www.vivapayments.com';

    /**
     * Demo environment accounts URL.
     */
    const DEMO_ACCOUNTS_URL = 'https://demo-accounts.vivapayments.com';

    /**
     * Production environment accounts URL.
     */
    const PRODUCTION_ACCOUNTS_URL = 'https://accounts.vivapayments.com';

    /**
     * Demo environment URL.
     */
    const DEMO_API_URL = 'https://demo-api.vivapayments.com';

    /**
     * Production environment URL.
     */
    const PRODUCTION_API_URL = 'https://api.vivapayments.com';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $gateway;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var string
     */
    protected $token;

    /**
     * Constructor.
     */
    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;

        $gateway = Payment::create('Vivawallet');
        $gateway->setAuthentication();
        $this->gateway = $gateway;

        if ($gateway->getTestMode()) {
            $this->environment = "demo";
        } else {
            $this->environment = "production";
        }
    }

    public function isSandbox()
    {
        return $this->environment === 'demo';
    }

    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * Make a GET request.
     *
     * @param string $url
     * @param array $options
     * @return \stdClass
     */
    public function get(string $url, array $options = [])
    {
        $response = $this->client->get($url, $options);

        return $this->getBody($response);
    }

    /**
     * Make a POST request.
     *
     * @param string $url
     * @param array $options
     * @return \stdClass
     */
    public function post(string $url, array $options = [])
    {
        $response = $this->client->post($url, $options);

        return $this->getBody($response);
    }

    /**
     * Make a PATCH request.
     *
     * @param string $url
     * @param array $options
     * @return \stdClass|null
     */
    public function patch(string $url, array $options = [])
    {
        $response = $this->client->patch($url, $options);

        return $this->getBody($response);
    }

    /**
     * Make a DELETE request.
     *
     * @param string $url
     * @param array $options
     * @return \stdClass
     */
    public function delete(string $url, array $options = [])
    {
        $response = $this->client->delete($url, $options);

        return $this->getBody($response);
    }

    /**
     * Get the response body.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \stdClass|null
     *
     * @throws \Corals\Modules\Payment\Vivawallet\Classes\VivaException
     */
    protected function getBody(ResponseInterface $response)
    {
        /** @var \stdClass|null $body */
        $body = json_decode($response->getBody(), false, 512, JSON_BIGINT_AS_STRING);

        if (isset($body->ErrorCode) && $body->ErrorCode !== 0) {
            throw new VivaException($body->ErrorText, $body->ErrorCode);
        }

        return $body;
    }

    /**
     * Get the URL.
     */
    public function getUrl(): UriInterface
    {
        $uris = [
            'production' => self::PRODUCTION_URL,
            'demo' => self::DEMO_URL,
        ];

        return new Uri($uris[$this->environment]);
    }

    /**
     * Get the accounts URL.
     */
    public function getAccountsUrl(): UriInterface
    {
        $uris = [
            'production' => self::PRODUCTION_ACCOUNTS_URL,
            'demo' => self::DEMO_ACCOUNTS_URL,
        ];

        return new Uri($uris[$this->environment]);
    }

    /**
     * Get the API URL.
     */
    public function getApiUrl(): UriInterface
    {
        $uris = [
            'production' => self::PRODUCTION_API_URL,
            'demo' => self::DEMO_API_URL,
        ];

        return new Uri($uris[$this->environment]);
    }

    /**
     * Get the Guzzle client.
     */
    public function getClient(): GuzzleClient
    {
        return $this->client;
    }

    /**
     * Authenticate using basic auth.
     */
    public function authenticateWithBasicAuth(): array
    {
        return [
            RequestOptions::AUTH => [
                $this->gateway->getMerchantId(),
                $this->gateway->getApiKey(),
            ],
        ];
    }

    /**
     * Authenticate using the public key as a query string.
     */
    public function authenticateWithPublicKey(): array
    {
        return [
            RequestOptions::QUERY => [
                'key' => $this->gateway->getPublicKey(),
            ],
        ];
    }

    /**
     * Authenticate using the bearer token as an authorization header.
     */
    public function authenticateWithBearerToken(): array
    {
        return [
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$this->token}",
            ],
        ];
    }

    public function withToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
