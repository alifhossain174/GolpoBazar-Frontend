@foreach($searchProducts as $searchProduct)
    <li class="live_search_item">
        <a class="live_search_product_link" href="{{url('book')}}/{{$searchProduct->slug}}">
            <img class="live_search_product_image lazy" src="{{url('assets')}}/img/product-load.gif" data-src="{{url(env('ADMIN_URL')."/".$searchProduct->image)}}" alt="">
            <h6 class="live_search_product_title">
                {{$searchProduct->name}}
                @if($searchProduct->discount_price > 0)
                    <span class="live_search_product_price"><del>৳{{number_format($searchProduct->price)}}</del> ৳{{number_format($searchProduct->discount_price)}}</span>
                @else
                    <span class="live_search_product_price">৳{{number_format($searchProduct->price)}}</span>
                @endif
            </h6>
        </a>
    </li>
@endforeach
