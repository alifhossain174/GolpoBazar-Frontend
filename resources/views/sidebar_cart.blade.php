@if(session('cart') && count(session('cart')))
    @foreach(session('cart') as $id => $details)

        @php
            $cartBookFullName = strip_tags($details['name']);
            $cartBookName = (mb_strlen($cartBookFullName, 'UTF-8') > 15) ? mb_substr($cartBookFullName, 0, 15, 'UTF-8') . "..." : $cartBookFullName;
        @endphp

        <li><a class="dropdown-item" href="#">{{$details['quantity']}} * {{$cartBookName}}</a></li>

    @endforeach
@else
    <li><a class="dropdown-item" href="#">No Items in Cart</a></li>
@endif
<li>
    <hr class="dropdown-divider">
</li>
<li class="text-center">
    <a class="btn btn-sm rounded d-inline-block checkout_btn" href="checkout.html"><i class="fas fa-shopping-cart"></i> View Cart</a>
</li>
