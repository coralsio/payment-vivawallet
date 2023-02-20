<?php

namespace Corals\Modules\Payment\Vivawallet\Messages;

use Corals\Modules\Payment\Common\Message\AbstractResponse;
use Corals\Modules\Payment\Common\Message\RequestInterface;

/**
 * Stripe Response.
 *
 * This is the response class for all Stripe requests.
 *
 * @see \Corals\Modules\Payment\Stripe\Gateway
 */
class Response extends AbstractResponse
{
    /**
     * Request id
     *
     * @var string URL
     */
    protected $requestId = null;

    /**
     * @var array
     */
    protected $headers = [];

    public function __construct(RequestInterface $request, $data, $headers = [])
    {
        $this->request = $request;
        $this->data = $data;
        $this->headers = $headers;

        parent::__construct($request, $data);
    }

    /**
     * Is the transaction successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return data_get($this->data, 'StatusId') == 'F';
    }

    /**
     * Get the transaction reference.
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        return data_get($this->data, 'TransactionId');
    }
}
