<div class="card book">
    <div class="image-container">
        <a href="{{url('book')}}/{{$book->slug}}">
            <img class="card-img-top book_image lazy" src="{{ url('assets') }}/images/product-load.gif"
            data-src="{{ url(env('ADMIN_URL') . '/' . $book->image) }}" alt="">
        </a>
        <div class="overlay">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col book_price">
                        @if($book->discount_price && $book->discount_price < $book->price)
                            <small><del>{{number_format($book->price)}}<sup>৳</sup></del></small>
                            {{number_format($book->discount_price)}}<sup>৳</sup>
                        @else
                            {{number_format($book->price)}}<sup>৳</sup>
                        @endif
                    </div>
                    <div class="col text-end">
                        <button data-id="{{$book->id}}" class="cart-{{$book->id}} addToCart add-to-cart-btn"><i class="fas fa-cart-plus"></i></button>
                        <button onclick="socialShare('{{$book->slug}}')" class="social-share-btn"><i class="fas fa-share-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <a href="{{url('book')}}/{{$book->slug}}">
            <h5 class="book_title">{{$book->name}}</h5>
            <p class="book_author">{{$book->author_name}}</p>

            @php
                $productReviews = DB::table('product_reviews')->where('product_id', $book->id)->get();
                $productRating = DB::table('product_reviews')->where('product_id', $book->id)->sum('rating');
            @endphp

            <div class="d-flex align-items-center">
                <div class="book_rating">

                    @if(count($productReviews) > 0)
                        @for ($i=1;$i<=round($productRating/count($productReviews));$i++)
                        <i class="fas fa-star rating"></i>
                        @endfor

                        @for ($i=1;$i<=5-round($productRating/count($productReviews));$i++)
                        <i class="far fa-star rating"></i>
                        @endfor
                    @else
                        <i class="far fa-star rating"></i>
                        <i class="far fa-star rating"></i>
                        <i class="far fa-star rating"></i>
                        <i class="far fa-star rating"></i>
                        <i class="far fa-star rating"></i>
                    @endif

                </div>
                <span class="book_reviews ms-2">({{count($productReviews)}})</span>
            </div>
        </a>
    </div>
</div>
