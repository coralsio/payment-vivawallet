<?php

namespace Corals\Modules\Payment\Vivawallet\Http\Controllers;

use Corals\Foundation\Models\BaseModel;
use Corals\Modules\Marketplace\Jobs\HandleOrdersWithPayouts;
use Corals\Modules\Payment\Vivawallet\Classes\WebhookController;
use Corals\User\Models\User;
use Illuminate\Http\Request;

class VivawalletController extends WebhookController
{
    protected function handleCreateTransaction(Request $request)
    {
        logger('handleCreateTransaction');

        $requestData = $request->all();

        logger($requestData);

        $eventData = data_get($requestData, 'EventData');

        $isSuccess = data_get($eventData, 'StatusId') == 'F';

        if ($isSuccess) {
            $transactionId = data_get($eventData, 'MerchantTrns');
            $payment_reference = data_get($eventData, 'TransactionId');

            $checkoutServiceClass = "Corals\\Modules\\{$this->namespace}\\Services\\CheckoutService";
            $checkoutService = new $checkoutServiceClass();

            $orderClass = "Corals\\Modules\\{$this->namespace}\\Models\\Order";
            /**
             * @var BaseModel $orderClass
             */
            $orders =
                $orderClass::query()
                    ->whereRaw("JSON_EXTRACT(billing, '$.payment_reference') = ?", $transactionId)->get();

            $orders->each(function ($order) use ($payment_reference, $checkoutService) {
                $payment_status = "paid";
                $order_status = "processing";

                $billing = $order->billing;

                $billing_address = data_get($billing, 'billing_address');
                $shipping_address = data_get($billing, 'shipping_address');

                $billing['payment_reference'] = $payment_reference;
                $billing['gateway'] = 'Vivawallet';
                $billing['payment_status'] = $payment_status;

                $order->update([
                    'status' => $order_status,
                    'billing' => $billing,
                ]);

                $user = $order->user ?? new User();

                $invoice = $checkoutService->generateOrderInvoice($order, $payment_status, $user, $billing_address);

                $checkoutService->setOrderShippingDetails($order, $shipping_address);

                $checkoutService->orderFulfillment($order, $invoice, $user);
            });

            if (\Settings::get('marketplace_payout_payout_mode') == "immediate") {
                foreach ($orders as $order) {
                    dispatch(new HandleOrdersWithPayouts($order));
                }
            }
        }
    }

    protected function handleRefundTransaction(Request $request)
    {
        logger('handleRefundTransaction');
        logger($request->all());
        // TODO: Implement handleRefundTransaction() method.
    }

    protected function handleEventNotification(Request $request)
    {
        logger('handleEventNotification');
        logger($request->all());
        // TODO: Implement handleEventNotification() method.
    }

    public function accountConnect(Request $request)
    {
        $request->validate([
            'account_id' => 'required_with:merchant_id',
            'merchant_id' => 'required_with:account_id'
        ]);

        try {
            $this->initController();

            $data = $request->only(['account_id', 'merchant_id']);

            $status = !empty($data['account_id']) ? 'PAYOUTS_ENABLED' : 'PAYOUTS_DISABLED';

            $referenceId = data_get($data, 'account_id');

            $gatewayStatus = user()->setGatewayStatus($this->gateway->getName(), $status, '', $referenceId,
                'AccountConnect', $data);

            $gatewayStatus->update(['object_reference' => $referenceId]);

            $message = [
                'level' => 'success',
                'message' => trans('Corals::messages.success.saved'),
                'action' => 'site_reload'
            ];
        } catch (\Exception $exception) {
            log_exception($exception, VivawalletController::class, 'accountConnectß');
            $message = ['level' => 'error', 'message' => $exception->getMessage()];
        }

        return response()->json($message);
    }

    public function onlinePaymentReturnResult(Request $request)
    {
        if ($request->is('*success')) {
            $status = 'success';
            \ShoppingCart::destroyAllCartInstances();
        } else {
            $status = 'failed';
        }

        return view('Vivawallet::online_payment_result')
            ->with(compact('status'));
    }
}
