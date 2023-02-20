<?php

return [
    'name' => 'Vivawallet',
    'key' => 'payment_vivawallet',
    'support_reservation' => false,
    'support_ecommerce' => true,
    'support_marketplace' => true,
    'support_subscription' => false,
    'support_online_refund' => true,
    'manage_remote_plan' => false,
    'require_token_confirm' => false,
    'manage_remote_product' => false,
    'manage_remote_sku' => false,
    'manage_remote_order' => false,
    'supports_swap' => false,
    'supports_swap_in_grace_period' => false,
    'require_invoice_creation' => false,
    'require_plan_activation' => false,
    'capture_payment_method' => false,
    'require_default_payment_set' => false,
    'can_update_payment' => false,
    'create_remote_customer' => false,
    'require_payment_token' => false,
    'support_connect_account' => true,
    'no_payment_details_required' => true,
    'settings' => [
        'live_merchant_id' => [
            'label' => 'Vivawallet::labels.settings.live_merchant_id',
            'type' => 'text',
            'required' => false,
        ],
        'live_primary_account_id' => [
            'label' => 'Vivawallet::labels.settings.live_primary_account_id',
            'type' => 'text',
            'required' => false,
        ],
        'live_api_key' => [
            'label' => 'Vivawallet::labels.settings.live_api_key',
            'type' => 'text',
            'required' => false,
        ],
        'live_public_key' => [
            'label' => 'Vivawallet::labels.settings.live_public_key',
            'type' => 'text',
            'required' => false,
        ],
        'live_client_id' => [
            'label' => 'Vivawallet::labels.settings.live_client_id',
            'type' => 'text',
            'required' => false,
        ],
        'live_client_secret' => [
            'label' => 'Vivawallet::labels.settings.live_client_secret',
            'type' => 'text',
            'required' => false,
        ],
        'sandbox_mode' => [
            'label' => 'Vivawallet::labels.settings.sandbox_mode',
            'type' => 'boolean'
        ],
        'sandbox_merchant_id' => [
            'label' => 'Vivawallet::labels.settings.sandbox_merchant_id',
            'type' => 'text',
            'required' => false,
        ],
        'sandbox_primary_account_id' => [
            'label' => 'Vivawallet::labels.settings.sandbox_primary_account_id',
            'type' => 'text',
            'required' => false,
        ],
        'sandbox_api_key' => [
            'label' => 'Vivawallet::labels.settings.sandbox_api_key',
            'type' => 'text',
            'required' => false,
        ],
        'sandbox_public_key' => [
            'label' => 'Vivawallet::labels.settings.sandbox_public_key',
            'type' => 'text',
            'required' => false,
        ],
        'sandbox_client_id' => [
            'label' => 'Vivawallet::labels.settings.sandbox_client_id',
            'type' => 'text',
            'required' => false,
        ],
        'sandbox_client_secret' => [
            'label' => 'Vivawallet::labels.settings.sandbox_client_secret',
            'type' => 'text',
            'required' => false,
        ],
        'connect_enabled' => [
            'label' => 'Vivawallet::labels.settings.connect_enabled',
            'type' => 'boolean'
        ],
    ],
    'events' => [
        'successful_payment' => \Corals\Modules\Payment\Vivawallet\Job\HandleSuccessfullPayment::class,
    ],
    'webhook_handler' => \Corals\Modules\Payment\Vivawallet\Gateway::class,

];
