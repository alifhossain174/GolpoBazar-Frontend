@if(session('cart') && count(session('cart')))
    @foreach(session('cart') as $id => $details)

        @php
            $cartBookFullName = strip_tags($details['name']);
            $cartBookName = (mb_strlen($cartBookFullName, 'UTF-8') > 20) ? mb_substr($cartBookFullName, 0, 20, 'UTF-8') . "..." : $cartBookFullName;
            $bookInfo = DB::table('products')
                            ->leftJoin('users', 'products.author_id', 'users.id')
                            ->select('products.*', 'users.name as author_name')
                            ->where('products.id', $id)
                            ->first();
        @endphp

        <li class="sidebar_cart_item">
            <a class="dropdown-item" href="javascript:void(0)">
                <img class="sidebar_cart_item_image" src="{{url(env('ADMIN_URL')."/".$bookInfo->image)}}" alt="">
                <div class="sidebar_cart_details m-0">
                    <h5 class="sidebar_cart_book_name">{{$cartBookName}}</h5>
                    <h6 class="sidebar_cart_book_author">{{$bookInfo->author_name}}</h6>

                    @if($bookInfo->discount_price > 0 && $bookInfo->discount_price < $bookInfo->price)
                        @if($bookInfo->discount_price == 0)
                            <span class="sidebar_cart_price">Free</span>
                        @else
                            <span class="sidebar_cart_price"><del style="font-size: 12px">{{number_format($bookInfo->price)}}<sup>৳</sup></del> {{number_format($bookInfo->discount_price)}}<sup>৳</sup></span>
                        @endif
                    @else
                        @if($bookInfo->price == 0)
                            <span class="sidebar_cart_price">Free</span>
                        @else
                            <span class="sidebar_cart_price">{{number_format($bookInfo->price)}}<sup>৳</sup></span>
                        @endif
                    @endif
                </div>
            </a>
        </li>

    @endforeach

    <li>
        <hr class="dropdown-divider">
    </li>
    <li class="text-center">
        <a class="btn btn-sm rounded d-inline-block checkout_btn" href="{{url('view/cart')}}"><i class="fas fa-shopping-cart"></i> Checkout</a>
    </li>

@else
    <li><a class="dropdown-item" href="#">No Items in Cart</a></li>
@endif

