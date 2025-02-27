@extends('layouts.front-end.app')

@section('title',\App\CPU\translate('Choose Payment Method'))

@push('css_or_js')
    <style>
        .stripe-button-el {
            display: none !important;
        }

        .razorpay-payment-button {
            display: none !important;
        }
    </style>

    {{--stripe--}}
    <script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
    <script src="https://js.stripe.com/v3/"></script>
    {{--stripe--}}
@endpush

@section('content')
    <!-- Page Content-->
    <div class="container pb-5 mb-2 mb-md-4 rtl"
         style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
        <div class="row">
            <div class="col-md-12 mb-5 pt-5">
                <div class="feature_header __feature_header">
                    <span>{{ \App\CPU\translate('payment_method')}}</span>
                </div>
            </div>
            <section class="col-lg-8">
                <div class="checkout_details">
                @include('web-views.partials._checkout-steps',['step'=>3])
                <!-- Payment methods accordion-->
                    <h2 class="h6 pb-3 mb-2 mt-5">{{\App\CPU\translate('choose_payment')}}</h2>

                    <div class="row g-3">
                        @if(!$cod_not_show && $cash_on_delivery['status'])
                            <div class="col-sm-6" id="cod-for-cart">
                                <div class="card cursor-pointer">
                                    <div class="card-body __h-100px">
                                        <form action="{{route('checkout-complete')}}" method="get" class="needs-validation">
                                            <input type="hidden" name="payment_method" value="cash_on_delivery">
                                            <button class="btn btn-block click-if-alone" type="submit">
                                                <img width="150" class="__mt-n-10" src="{{asset('public/assets/front-end/img/cod.png')}}"/>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($digital_payment['status']==1)
                            @if($wallet_status==1)
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <button class="btn btn-block click-if-alone" type="submit"
                                                data-toggle="modal" data-target="#wallet_submit_button">
                                                <img width="150" class="__mt-n-10"
                                                    src="{{asset('public/assets/front-end/img/wallet.png')}}"/>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($offline_payment) && $offline_payment['status'])
                                <div class="col-sm-6" id="cod-for-cart">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <form action="{{route('offline-payment-checkout-complete')}}" method="get" class="needs-validation">
                                                <span class="btn btn-block click-if-alone"
                                                        data-toggle="modal" data-target="#offline_payment_submit_button">
                                                    <img width="150" class="__mt-n-10" src="{{asset('public/assets/front-end/img/pay-offline.png')}}"/>
                                                </span>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($stripe['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <button class="btn btn-block click-if-alone" type="button" id="checkout-button">
                                                {{-- <i class="czi-card"></i> {{\App\CPU\translate('Credit / Debit card ( Stripe )')}} --}}
                                                <img width="150"
                                                src="{{asset('public/assets/front-end/img/stripe.png')}}"/>
                                            </button>
                                            <script type="text/javascript">
                                                // Create an instance of the Stripe object with your publishable API key
                                                var stripe = Stripe('{{$stripe['published_key']}}');
                                                var checkoutButton = document.getElementById("checkout-button");
                                                checkoutButton.addEventListener("click", function () {
                                                    fetch("{{route('pay-stripe')}}", {
                                                        method: "GET",
                                                    }).then(function (response) {
                                                        console.log(response)
                                                        return response.text();
                                                    }).then(function (session) {
                                                        /*console.log(JSON.parse(session).id)*/
                                                        return stripe.redirectToCheckout({sessionId: JSON.parse(session).id});
                                                    }).then(function (result) {
                                                        if (result.error) {
                                                            alert(result.error.message);
                                                        }
                                                    }).catch(function (error) {
                                                        console.error("{{\App\CPU\translate('Error')}}:", error);
                                                    });
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                    </div>
                    <!-- Navigation (desktop)-->
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center mt-5">
                            <a class="btn btn-secondary btn-block" href="{{route('checkout-details')}}">
                                <span class="d-none d-sm-inline">{{\App\CPU\translate('Back to Shipping')}}</span>
                                <span class="d-inline d-sm-none">{{\App\CPU\translate('Back')}}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Sidebar-->
            @include('web-views.partials._order-summary')
        </div>
    </div>

    <!-- wallet modal -->
  <div class="modal fade" id="wallet_submit_button" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">{{\App\CPU\translate('wallet_payment')}}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @php($customer_balance = auth('customer')->user()->wallet_balance)
        @php($remain_balance = $customer_balance - $amount)
        <form action="{{route('checkout-complete-wallet')}}" method="get" class="needs-validation">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="">{{\App\CPU\translate('your_current_balance')}}</label>
                        <input class="form-control" type="text" value="{{\App\CPU\Helpers::currency_converter($customer_balance)}}" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="">{{\App\CPU\translate('order_amount')}}</label>
                        <input class="form-control" type="text" value="{{\App\CPU\Helpers::currency_converter($amount)}}" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="">{{\App\CPU\translate('remaining_balance')}}</label>
                        <input class="form-control" type="text" value="{{\App\CPU\Helpers::currency_converter($remain_balance)}}" readonly>
                        @if ($remain_balance<0)
                        <label class="__color-crimson">{{\App\CPU\translate('you do not have sufficient balance for pay this order!!')}}</label>
                        @endif
                    </div>
                </div>

            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{\App\CPU\translate('close')}}</button>
            <button type="submit" class="btn btn--primary" {{$remain_balance>0? '':'disabled'}}>{{\App\CPU\translate('submit')}}</button>
            </div>
        </form>
      </div>
    </div>
  </div>

    <!-- offline payment modal -->
  <div class="modal fade" id="offline_payment_submit_button" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">{{\App\CPU\translate('offline_payment')}}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{route('offline-payment-checkout-complete')}}" method="post" class="needs-validation">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="">{{\App\CPU\translate('payment_by')}}</label>
                        <input class="form-control" type="text" name="payment_by" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="">{{\App\CPU\translate('transaction_ID')}}</label>
                        <input class="form-control" type="text" name="transaction_ref" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="">{{\App\CPU\translate('payment_note')}}</label>
                        <textarea name="payment_note" id="" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" value="offline_payment" name="payment_method">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{\App\CPU\translate('close')}}</button>
            <button type="submit" class="btn btn--primary">{{\App\CPU\translate('submit')}}</button>
            </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('script')
    <script>
        setTimeout(function () {
            $('.stripe-button-el').hide();
            $('.razorpay-payment-button').hide();
        }, 10)
    </script>

    <script type="text/javascript">
        function click_if_alone() {
            let total = $('.checkout_details .click-if-alone').length;
            if (Number.parseInt(total) < 2) {
                $('.click-if-alone').click()
                $('.checkout_details').html('<h1>{{\App\CPU\translate('Redirecting_to_the_payment')}}......</h1>');
            }
        }
        click_if_alone();

    </script>
@endpush
