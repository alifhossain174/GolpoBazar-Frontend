@extends('master')

@push('site-seo')
    @php
        $generalInfo = DB::table('general_infos')->where('id', 1)->first();
    @endphp
    <title>
        @if ($generalInfo && $generalInfo->tab_title)
            {{ $generalInfo->tab_title }}
        @else
            {{ $generalInfo->company_name }}
        @endif
    </title>
    @if ($generalInfo && $generalInfo->fav_icon)
        <link rel="icon" href="{{ env('ADMIN_URL') . '/' . $generalInfo->fav_icon }}" type="image/x-icon" />
    @endif
@endpush

@section('header_css')
    <style>
        li.cartViewList .cartRemoveBtn {
            width: 5%;
            margin-right: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        li.cartViewList .cartRemoveBtn button {
            padding: 6px;
            color: white;
        }

        li.cartViewList a.cartItemImage {
            width: 10%;
        }

        li.cartViewList a.cartItemImage img {
            height: 70px;
            width: 48px;
            margin-right: 10px;
        }

        li.cartViewList .cartItemInfo {
            width: 77%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        li.cartViewList .cartItemPrice {
            width: 8%;
            line-height: 60px;
            text-align: right;
        }

        @media (max-width: 576px) {
            li.cartViewList .cartRemoveBtn {
                width: 8%;
            }

            li.cartViewList a.cartItemImage {
                width: 16%;
            }

            li.cartViewList .cartItemInfo {
                width: 64%;
            }

            li.cartViewList .cartItemPrice {
                width: 12%;
            }
        }

        /* css for payment method */
        .checkout-payment-method-inner {
            margin: 20px 0;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .payment-method-input {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .payment-method-input label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            background-color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .payment-method-input label:hover {
            border-color: #d1d1d1;
            background-color: #f5f5f5;
        }

        .payment-method-input-main {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            color: #333;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            cursor: pointer;
            accent-color: #0066cc;
        }

        .payment-method-input label img {
            object-fit: contain;
        }

        /* Selected payment method style */
        .payment-method-input label:has(.form-check-input:checked) {
            border-color: #0066cc;
            background-color: #f0f7ff;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .payment-method-input label {
                padding: 10px;
            }

            .payment-method-input-main {
                font-size: 14px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <main>
            <div class="row g-5">
                <div class="col-md-7 col-lg-7">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                        <span style="color: #20B1B6; font-weight: 600;">Your cart</span>
                        <span style="background: #20B1B6;"
                            class="badge rounded-pill view_cart_count">{{ session('cart') ? count(session('cart')) : 0 }}</span>
                    </h4>
                    <ul class="list-group mb-3" id="view_cart_items">
                        @include('cart_items')
                    </ul>

                    {{-- <form class="card p-2">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Promo code">
                        <button type="button" class="btn btn-secondary">Redeem</button>
                    </div>
                </form> --}}
                </div>

                <div class="col-md-5 col-lg-5">
                    <h4 class="mb-3">Make Payment</h4>
                    <form action="{{url('place/order')}}" class="needs-validation" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" name="phone" value="" class="form-control" id="phone" placeholder="+8801XXXXXXXXX" required readonly>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h4 class="mb-3">Payment Method</h4>

                        @php
                            $paymentGateways = DB::table('payment_gateways')->get();
                        @endphp


                        <div class="checkout-payment-method-inner single-details-box-inner">
                            <div class="payment-method-input">

                                @if ($paymentGateways[0]->status == 1)
                                    <label for="flexRadioDefault2">
                                        <div class="payment-method-input-main">
                                            <input class="form-check-input" type="radio" name="payment_method" value="sslcommerz" id="flexRadioDefault2" required />
                                            SSLCommerz
                                        </div>
                                        <img alt="SSLCommerz" src="{{ url(env('ADMIN_URL') . '/images/ssl_commerz.png') }}"
                                            style="max-width: 90px;" />
                                    </label>
                                @endif

                                @if ($paymentGateways[2]->status == 1)
                                    <label for="flexRadioDefault3">
                                        <div class="payment-method-input-main">
                                            <input class="form-check-input" type="radio" name="payment_method" value="bkash" id="flexRadioDefault3" required />
                                            bKash Payment
                                        </div>
                                        <img alt="bKash Payment"
                                            src="{{ url(env('ADMIN_URL') . '/images/bkash_payment_gateway.png') }}"
                                            style="max-width: 45px;" />
                                    </label>
                                @endif

                            </div>
                        </div>

                        <hr class="my-4">
                        <button class="w-100 btn btn-lg text-white" style="background-color: #20B1B6;" type="submit"><i class="fas fa-money-bill-alt"></i> Pay Now</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
@endsection
