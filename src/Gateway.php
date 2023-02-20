<?php

namespace Corals\Modules\Payment\Vivawallet;

use Corals\Modules\Payment\Common\AbstractGateway;
use Corals\Modules\Payment\Common\Models\Transaction;
use Corals\Modules\Payment\Common\Models\WebhookCall;
use Corals\Modules\Payment\Vivawallet\Messages\CreateOrderMessage;
use Corals\Modules\Payment\Vivawallet\Messages\ObligationMessage;
use Corals\Modules\Payment\Vivawallet\Messages\RefundTransactionMessage;
use Corals\Modules\Payment\Vivawallet\Messages\TransferMessage;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Vivawallet Class
 */
class Gateway extends AbstractGateway
{

    use ValidatesRequests;

    public function getName()
    {
        return 'Vivawallet';
    }


    public function setAuthentication()
    {
        $merchantId = '';
        $apiKey = '';
        $publicKey = '';

        $sandbox = $this->getSettings('sandbox_mode', 'true');

        if ($sandbox == 'true') {
            $merchantId = $this->getSettings('sandbox_merchant_id');
            $apiKey = $this->getSettings('sandbox_api_key');
            $publicKey = $this->getSettings('sandbox_public_key');
            $this->setTestMode(true);
        } elseif ($sandbox == 'false') {
            $merchantId = $this->getSettings('live_merchant_id');
            $apiKey = $this->getSettings('live_api_key');
            $publicKey = $this->getSettings('live_public_key');

            $this->setTestMode(false);
        }

        $this->setMerchantId($merchantId);
        $this->setApiKey($apiKey);
        $this->setPublicKey($publicKey);
    }

    public function setMerchantId($merchantId)
    {
        return $this->setParameter('merchant_id', $merchantId);
    }

    public function setApiKey($apiKey)
    {
        return $this->setParameter('api_key', $apiKey);
    }

    public function setPublicKey($publicKey)
    {
        return $this->setParameter('public_key', $publicKey);
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchant_id');
    }

    public function getApiKey()
    {
        return $this->getParameter('api_key');
    }

    public function getPublicKey()
    {
        return $this->getParameter('public_key');
    }

    public function getPaymentViewName()
    {
        return 'Vivawallet::vivawallet_selected';
    }

    public function prepareCreateRefundParameters($order, $amount)
    {
        return [
            'amount' => $amount,
            'transactionId' => $order->billing['payment_reference'],
        ];
    }

    /**
     * @param array $parameters
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest(RefundTransactionMessage::class, $parameters);
    }

    public function getPaymentRedirectContent($data = [])
    {
        tap(Validator::make($data, [
            'redirectHandler' => 'required',
            'paymentPurpose' => 'required',
            'transactionId' => 'required',
            "amount" => 'required|gt:0',
            "currency" => 'required',
        ]), function (\Illuminate\Contracts\Validation\Validator $validator) {
            $validator->validate();
        });


        $this->setAuthentication();

        $billing_address = \ShoppingCart::get('default')->getAttribute('billing_address');

        $amountInAdminCurrency = \Payments::currency_convert(data_get($data, 'amount'));

        $createOrderRequest = $this->createRequest(CreateOrderMessage::class, [
            'amount' => $amountInAdminCurrency,
            'details' => [
                'fullName' => sprintf("%s %s",
                    data_get($billing_address, 'first_name'),
                    data_get($billing_address, 'last_name')
                ),
                'email' => data_get($billing_address, 'email'),
                'sourceCode' => 'Default',
                'merchantTrns' => Str::limit(data_get($data, 'transactionId'), 50, ''),
                'customerTrns' => Str::limit(data_get($data, 'paymentPurpose'), 255, ''),
            ]
        ]);

        $checkoutUrl = $createOrderRequest->send();

        return view('Vivawallet::payment_page')->with(compact('checkoutUrl'));
    }

    public function requireRedirect()
    {
        return true;
    }

    public function getCountriesBanks($country = "")
    {
        $response = json_decode((new HttpClient())->get('https://psd2.vivawallet.lt/api/countries')->getBody(), true);
        $countries = collect($response);
        if ($country) {
            $countries = $countries->where('code', $country);
        }
        return $countries;
    }

    public function validateRequest($request)
    {
        if ($this->getSettings('show_banks_select', false)) {
            return $this->validate($request, [
                'payment_details.selected_bank' => 'required',
            ], [
                'payment_details.selected_bank.required' => trans('Vivawallet::labels.validation.select_bank'),
            ]);
        }
    }

    public static function webhookHandler(Request $request)
    {
        try {
            $webhookCall = null;


            $eventPayload = $request->input();
            $data = [
                'event_name' => 'vivawallet.successful_payment',
                'payload' => $eventPayload,
                'gateway' => 'Vivawallet'
            ];
            $webhookCall = WebhookCall::create($data);
            $webhookCall->process();
            return json_encode(['status' => 'success']);
        } catch (\Exception $exception) {
            if ($webhookCall) {
                $webhookCall->saveException($exception);
            }
            log_exception($exception, 'Webhooks', 'vivawallet');
        }
    }

    public function getConnectAccountSettings($gatewayStatus)
    {
        $connect = [
            'connect_url' => url('viva/account-connect'),
            'gateway_title' => $this->getName(),
            'gateway' => optional($gatewayStatus)->gateway,
            'status' => optional($gatewayStatus)->status,
            'merchant_id' => optional($gatewayStatus)->getProperty('merchant_id'),
            'account_id' => optional($gatewayStatus)->object_reference,
            'gateway_status_id' => optional($gatewayStatus)->id,
        ];

        return view('Vivawallet::account_connect')
            ->with(compact('connect'));
    }

    public function transfer($parameters)
    {
        $sandbox = $this->getSettings('sandbox_mode', 'true');

        $parameters['walletId'] = $this->getSettings(($sandbox == 'true' ? 'sandbox_' : 'live_') . 'primary_account_id');

        return $this->createRequest(TransferMessage::class, $parameters);
    }

    public function obligation($parameters)
    {
        return $this->createRequest(ObligationMessage::class, $parameters);
    }

    public function reverseTransfer($parameters)
    {
        $transactionRef = data_get($parameters, 'transferReference');
        unset($parameters['transferReference']);
        /**
         * @var Transaction $transaction
         */
        $transaction = Transaction::query()->where('reference', $transactionRef)->firstOrFail();

        $order = $transaction->sourcable;

        $store = $order->store;

        $user = $store->user;

        if (!$user) {
            throw new \Exception('Invalid store user');
        }

        $gatewayStatus = $user->getGatewayStatus($this->getName(), 'AccountConnect', true)->first();

        if (!$gatewayStatus || $gatewayStatus->status !== 'PAYOUTS_ENABLED') {
            throw new \Exception('Missing gateway status or Payout is disabled');
        }

        $sandbox = $this->getSettings('sandbox_mode', 'true');

        $settingPrefix = $sandbox == 'true' ? 'sandbox_' : 'live_';

        $parameters['destination'] = $this->getSettings($settingPrefix . 'primary_account_id');
        $parameters['walletId'] = $gatewayStatus->object_reference;
        $parameters['amount'] = abs($transaction->amount);

        //TODO check if obligation needed here or normal transfer

        return $this->createRequest(TransferMessage::class, $parameters);
    }
}
