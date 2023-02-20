@extends('layouts.public')

@section('editable_content')
    <div class="container">
        <div class="row">
            <div class="col-md-12 py-5 text-center">
                @if($status === 'success')
                    <i class="fa fa-5x fa-check d-block text-success"></i>
                    <h3>
                        @lang('Vivawallet::labels.order_paid_successfully')
                    </h3>

                    @auth
                        <a href="{{ url('marketplace/orders/my') }}"
                           class="btn btn-success">@lang('Vivawallet::labels.go_my_order')</a>
                        <br><br>
                    @else
                        <h5 class="text text-info">
                            @lang('Vivawallet::labels.order_guest_email_sent')
                        </h5>
                    @endauth
                @else
                    <i class="fa fa-5x fa-info d-block text-warning"></i>
                    <h3>
                        @lang('Vivawallet::labels.order_payment_failed')
                    </h3>
                @endif
            </div>
        </div>
    </div>
@endsection
