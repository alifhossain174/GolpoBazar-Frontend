@if(session('cart') && count(session('cart')))
@foreach(session('cart') as $id => $details)
<li class="cartViewList list-group-item d-flex justify-content-between lh-sm">

    <div class="cartRemoveBtn">
        <button data-id="{{$id}}" class="cart-{{$id}} removeFromCart btn btn-sm add-to-cart-btn"><i class="fas fa-trash-alt"></i></button>
    </div>

    <a href="{{url('book')}}/{{$details['slug']}}" class="cartItemImage">
        <img class="lazy" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url(env('ADMIN_URL') . '/' . $details['image']) }}" alt="">
    </a>
    <div class="cartItemInfo">
        <h6 style="margin-bottom: 4px;">{{$details['quantity']}} * {{$details['name']}}</h6>
        <small class="text-body-secondary">{{$details['author_name']}}</small>
    </div>
    <span class="cartItemPrice" class="text-body-secondary">
        @if($details['discount_price'] && $details['discount_price'] < $details['price'])
            <small><del>{{number_format($details['price'])}}<sup>৳</sup></del></small>
            {{number_format($details['discount_price'])}}<sup>৳</sup>
        @else
            {{number_format($details['price'])}}<sup>৳</sup>
        @endif
    </span>
</li>
@endforeach
@else
<li class="list-group-item d-flex justify-content-between lh-sm">
    <div class="pt-1 pb-1">
        <h6 class="my-0">No Books found in Cart</h6>
    </div>
</li>
@endif

@if(session('discount') && session('discount') > 0)
<li class="list-group-item d-flex justify-content-between bg-body-tertiary">
    <div class="text-success">
        <h6 class="my-0">Promo code</h6>
        <small>{{session('coupon')}}</small>
    </div>
    <span class="text-success">− {{session('discount')}}<sup>৳</sup></span>
</li>
@endif

<li class="p-3 list-group-item d-flex justify-content-between">
    @php $cartTotal = 0 @endphp
    @foreach((array) session('cart') as $id => $details)
        @php
            $cartTotal += ($details['discount_price'] > 0 ? $details['discount_price'] : $details['price']) * $details['quantity']
        @endphp
    @endforeach

    @php
        if(session('discount') && session('discount') > 0){
            $cartTotal = $cartTotal - session('discount');
        }
    @endphp

    <strong>Total Price</strong>
    <strong>{{number_format($cartTotal)}}<sup>৳</sup></strong>
</li>
