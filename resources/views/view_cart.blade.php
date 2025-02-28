@extends('master')

@section('header_css')
    <style>
        li.cartViewList .cartRemoveBtn {
            width: 5%;
            margin-right: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        li.cartViewList .cartRemoveBtn button{
            padding: 6px;
            color: white;
        }

        li.cartViewList a.cartItemImage {
            width: 10%;
        }

        li.cartViewList a.cartItemImage img{
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

        li.cartViewList .cartItemPrice{
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
            li.cartViewList .cartItemPrice{
                width: 12%;
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
                    <span style="background: #20B1B6;" class="badge rounded-pill view_cart_count">{{ session('cart') ? count(session('cart')) : 0 }}</span>
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
                <form class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" placeholder="+8801XXXXXXXXX" required>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h4 class="mb-3">Payment Method</h4>

                    <div class="row gy-3">
                        <div class="col-md-6">
                            <label for="cc-name" class="form-label">Name on card</label>
                            <input type="text" class="form-control" id="cc-name" placeholder="" required>
                            <small class="text-body-secondary">Full name as displayed on card</small>
                            <div class="invalid-feedback">
                                Name on card is required
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="cc-number" class="form-label">Credit card number</label>
                            <input type="text" class="form-control" id="cc-number" placeholder="" required>
                            <div class="invalid-feedback">
                                Credit card number is required
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <button class="w-100 btn btn-lg text-white" style="background-color: #20B1B6;" type="submit">Pay Now</button>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
