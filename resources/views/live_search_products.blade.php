@foreach($searchProducts as $searchProduct)
    <li class="live_search_item">
        <a class="live_search_product_link" href="{{url('book')}}/{{$searchProduct->slug}}">
            <img class="live_search_product_image lazy" src="{{url('assets')}}/img/product-load.gif" data-src="{{url(env('ADMIN_URL')."/".$searchProduct->image)}}" alt="">
            <div class="book_details m-0" style="position: relative">
                <h6 class="live_search_product_title">
                    {{$searchProduct->name}}
                </h6>
                <h5 class="live_search_book_author">{{$searchProduct->author_name}}</h5>
                @if($searchProduct->discount_price > 0 && $searchProduct->discount_price < $searchProduct->price)
                    @if($searchProduct->discount_price == 0)
                        <span class="live_search_product_price">Free</span>
                    @else
                        <span class="live_search_product_price"><del style="font-size: 14px;">{{number_format($searchProduct->price)}}<sup>৳</sup></del> {{number_format($searchProduct->discount_price)}}<sup>৳</sup></span>
                    @endif
                @else
                    @if($searchProduct->price == 0)
                        <span class="live_search_product_price">Free</span>
                    @else
                        <span class="live_search_product_price">{{number_format($searchProduct->price)}}<sup>৳</sup></span>
                    @endif
                @endif

                <span class="bookTag" style="position: absolute; bottom: 0; right: 0;">book</span>
            </div>
        </a>
    </li>
@endforeach

@foreach($searchAuthors as $searchAuthor)
    <li class="live_search_item">
        <a class="live_search_product_link" href="{{url('author/books')}}/{{$searchAuthor->id}}">

            @if($searchAuthor->image)
                <img class="live_search_product_image lazy" style="width: 45px; height: 45px; min-width: 45px; min-height: 45px;" src="{{url('assets')}}/images/authors/author.png" data-src="{{ url(env('ADMIN_URL') . '/' . $searchAuthor->image) }}" alt="">
            @else
                <img class="live_search_product_image" style="width: 45px; height: 45px; min-width: 45px; min-height: 45px;" src="{{url('assets')}}/images/authors/author.png" alt="">
            @endif

            <div class="book_details m-0" style="position: relative">
                <h6 class="live_search_product_title">{{$searchAuthor->name}}</h6>
                <span class="live_search_product_price" style="font-size: 12px;">Total Books: {{DB::table('products')->where('author_id', $searchAuthor->id)->where('status', 1)->count()}}</span>

                <span class="bookTag" style="position: absolute; bottom: 0; right: 0;">author</span>
            </div>

        </a>
    </li>
@endforeach

@if(count($searchProducts) == 0 && count($searchAuthors) == 0)
    <li class="live_search_item">
        <a class="live_search_product_link" href="javascript:void(0)">
            <div class="book_details m-0" style="position: relative">
                <h6 class="live_search_product_title">No result found</h6>
            </div>
        </a>
    </li>
@endif
