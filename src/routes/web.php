<?php

use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'viva'], function () {
    Route::get('online-payment/success', 'VivawalletController@onlinePaymentReturnResult');
    Route::get('online-payment/failed', 'VivawalletController@onlinePaymentReturnResult');
    Route::post('account-connect', 'VivawalletController@accountConnect');
    Route::match(['get', 'post'], 'webhooks', 'VivawalletController@handle')
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
});
