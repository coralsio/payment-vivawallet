<div class="py-2">
    {{ $connect['gateway_title'] }}
    @if($connect['status'] === 'PAYOUTS_ENABLED')
        {!! formatStatusAsLabels($connect['status'],['level'=>'success']) !!}
    @elseif($connect['status'])
        {!! formatStatusAsLabels($connect['status'],['level'=>'warning']) !!}
    @endif

    @if(!$connect['status'] || $connect['status'] !== 'PAYOUTS_ENABLED')
        <p class="alert alert-info">
            @lang('Marketplace::labels.connect_accounts.not_connected')
        </p>
    @endif
    <div class="mt-3">
        {!! CoralsForm::openForm(null, ['url'=>$connect['connect_url'],'method'=>'post']) !!}
        {!! CoralsForm::text('merchant_id', 'Vivawallet::labels.merchant_id', false, $connect['merchant_id']) !!}
        {!! CoralsForm::text('account_id', 'Vivawallet::labels.account_id', false, $connect['account_id']) !!}
        {!! CoralsForm::formButtons(
            trans('Vivawallet::labels.connect_account', ['gateway' => $connect['gateway_title']]),
            ['wrapper_class'=>'form-group'],['show_cancel'=>false]) !!}

        {!! CoralsForm::closeForm() !!}
    </div>
</div>
