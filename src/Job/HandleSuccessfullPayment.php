<?php

namespace Corals\Modules\Payment\Vivawallet\Job;


use Corals\Modules\Ecommerce\Models\Order as eCommerceOrder;
use Corals\Modules\Ecommerce\Services\CheckoutService as eCommerceCheckoutService;
use Corals\Modules\Marketplace\Models\Order as MarketplaceOrder;
use Corals\Modules\Marketplace\Services\CheckoutService as MarketplaceCheckoutService;
use Corals\Modules\Payment\Common\Models\WebhookCall;
use Corals\Modules\Payment\Vivawallet\Classes\JWT;
use Corals\Modules\Payment\Vivawallet\Exception\VivawalletWebhookFailed;
use Corals\Modules\Payment\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleSuccessfullPayment implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Corals\Modules\Payment\Common\Models\WebhookCall
     */
    public $webhookCall;

    /**
     * HandleInvoiceCreated constructor.
     * @param WebhookCall $webhookCall
     */
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle()
    {
        logger('Payment Succeeded');

        try {
            if ($this->webhookCall->processed) {
                throw VivawalletWebhookFailed::processedCall($this->webhookCall);
            }

            $payload = $this->webhookCall->payload;

            $gateway = Payment::create('Vivawallet');
            $gateway->setAuthentication();

            $jwt = app()->make(JWT::class);

            $tokenDecode = $jwt->decode($payload['token'], $gateway->getProjectKey());

            /*
                        $tokenDecode = [
                            "transactions" => [
                                "ec-gc31Zou66zzHZCfG" => [
                                    "status" => "success",
                                    "action" => "signed",
                                    "currency" => "EUR",
                                    "amount" => 0.1,
                                    "paymentPurpose" => "NIPS1-TS837 Payment purpose",
                                    "payerAccountNumber" => "LT1234567891234568",
                                    "receiverName" => "Petras Petraitis",
                                    "receiverAccountNumber" => "LT633520037062057581"
                                ]

                            ]
                        ];
            */

            if (is_array($tokenDecode) && is_array($tokenDecode['transactions'])) {

                foreach ($tokenDecode['transactions'] as $transaction_id => $transaction_data) {

                    $payment_status = $transaction_data['status'];
                    if ($payment_status != "success") {
                        throw VivawalletWebhookFailed::invalidSuccessStatus($this->webhookCall);

                    }
                    if (strpos($transaction_id, 'mp-') !== false) {

                        $orders = MarketplaceOrder::query()
                            ->whereRaw("JSON_extract(marketplace_orders.`billing`,'$.payment_reference') = '$transaction_id' ")->get();
                        foreach ($orders as $order) {

                            $checkoutService = new MarketplaceCheckoutService();

                            $billing = $order->billing;
                            $shipping = $order->shipping;
                            $billing['gateway'] = $gateway->getName();
                            $billing['payment_status'] = 'paid';

                            $order->update([
                                'status' => 'processing',
                                'billing' => $billing,
                            ]);

                            $invoice = $checkoutService->generateOrderInvoice($order, $payment_status, $order->user, $billing['billing_address']);

                            $checkoutService->setOrderShippingDetails($order, $shipping['shipping_address']);

                            $checkoutService->orderFulfillment($order, $invoice, $order->user);
                        }
                    } else if (strpos($transaction_id, 'ec-') !== false) {
                        $order = eCommerceOrder::query()
                            ->whereRaw("JSON_extract(ecommerce_orders.`billing`,'$.payment_reference') = '$transaction_id' ")->first();
                        if ($order) {
                            $checkoutService = new eCommerceCheckoutService();

                            $billing = $order->billing;
                            $billing_address = $billing['billing_address'];
                            $shipping_address = $order->shipping['shipping_address'];
                            $billing['gateway'] = $gateway->getName();
                            $billing['payment_status'] = 'paid';

                            $order->update([
                                'status' => 'processing',
                                'billing' => $billing,
                            ]);
                            $invoice = $checkoutService->generateOrderInvoice($order, $payment_status, $order->user, $billing_address);

                            $checkoutService->setOrderShippingDetails($order, $shipping_address);

                            $checkoutService->orderFulfillment($order, $invoice, $order->user);
                        }
                    }
                }
            } else {
                throw VivawalletWebhookFailed::invalidVivawalletPayload($this->webhookCall);
            }
            $this->webhookCall->markAsProcessed();
        } catch (\Exception $exception) {
            log_exception($exception);
            $this->webhookCall->saveException($exception);
            throw $exception;
        }
    }


}

