<!-- random pick -->
<section>
    <div class="container">
        <div class="todays_choice">
            <div class="row">
                <div class="col-lg-1">
                    <h4>Random Pick</h4>
                </div>
                <div class="col-lg-3 text-center">
                    <img class="img-fluid lazy" src="{{ url('assets') }}/images/product-load.gif"
                    data-src="{{ url(env('ADMIN_URL') . '/' . $randomBook->image) }}" alt="">
                </div>
                <div class="col-lg-8">
                    <div class="todays_choice_content">
                        <a href="{{url('book')}}/{{$randomBook->slug}}" class="d-inline-block todays_choice_book_title">{{$randomBook->name}}</a><br>
                        <a href="{{url('author/books')}}/{{$randomBook->author_id}}" class="d-inline-block todays_choice_book_author">{{$randomBook->author_name}}</a><br>

                        @php
                            $productReviews = DB::table('product_reviews')->where('product_id', $randomBook->id)->get();
                            $productRating = DB::table('product_reviews')->where('product_id', $randomBook->id)->sum('rating');
                        @endphp

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

                        <p>
                            @php
                                $randBookFullDes = strip_tags($randomBook->description);
                                $randomBookDescription = (mb_strlen($randBookFullDes, 'UTF-8') > 400) ? mb_substr($randBookFullDes, 0, 400, 'UTF-8') . "..." : $randBookFullDes;
                            @endphp
                            {{$randomBookDescription}}
                        </p>

                        <h6 class="categories" style="margin-bottom: 4px">Category</h6>
                        <span class="badge text-bg-secondary">{{$randomBook->category_name}}</span>

                        <h5 class="price">
                            @if($randomBook->discount_price && $randomBook->discount_price < $randomBook->price)
                                @if($randomBook->discount_price == 0)
                                    <span>Free</span>
                                @else
                                    <small><del>{{number_format($randomBook->price)}}<sup>৳</sup></del></small>
                                    {{number_format($randomBook->discount_price)}}<sup>৳</sup>
                                @endif
                            @else
                                @if($randomBook->price == 0)
                                    <span>Free</span>
                                @else
                                    {{number_format($randomBook->price)}}<sup>৳</sup>
                                @endif
                            @endif
                        </h5>

                        @php
                            // $bookURL = env('APP_URL')."/book/".$book->slug;
                            $bookURL = "https://golpobazar.com/book/".$randomBook->slug;
                            $packageName = "app.gstl.golpobazar";
                            $playStoreURL = "https://play.google.com/store/apps/details?id=" . $packageName;
                            $encodedFallbackURL = urlencode($playStoreURL);
                        @endphp

                        <style>
                            a.readBook{
                                border: 2px solid #20B1B6;
                                padding: 4px 16px;
                                font-size: 14px;
                                font-weight: 600;
                                line-height: 26px;
                                color: #20B1B6;
                                text-shadow: none;
                                transition: all 0.3s linear;
                                box-shadow: inset 2px 2px 5px #c6c6c6;
                            }

                            a.readBook:hover{
                                border: 2px solid #20B1B6;
                                text-shadow: none;
                                color: #20B1B6;
                                text-shadow: none;
                                box-shadow: none;
                            }
                        </style>

                        <a class="btn btn-sm rounded readBook d-inline-block mb-2" href="intent://{{ $bookURL }}#Intent;scheme=https;package={{ $packageName }};S.browser_fallback_url={{ $encodedFallbackURL }};end;"
                        onclick="return handleAppLink(event, '{{ $bookURL }}', '{{ $playStoreURL }}');">
                        @if($randomBook->is_audio == 1) <i class="fas fa-volume-up"></i> &nbsp;বইটি শুনুন @else <i class="fas fa-book-open"></i> &nbsp;বইটি পড়ুন @endif
                        </a>
                        <br>

                        <script>
                            function handleAppLink(event, bookURL, fallbackURL) {
                                if (!navigator.userAgent.match(/Android/i)) {
                                    // If not on Android, open the web link instead
                                    event.preventDefault();
                                    // window.location.href = bookURL;
                                    window.location.href = 'https://play.google.com/store/apps/details?id=app.gstl.golpobazar&hl=en';
                                }
                            }
                        </script>

                        @if (isset(session()->get('cart')[$randomBook->id]))
                            <button data-id="{{$randomBook->id}}" class="cart-{{$randomBook->id}} removeFromCart btn add_to_cart"><i class="fas fa-times"></i> Remove from Cart</button>
                        @else
                            <button data-id="{{$randomBook->id}}" class="cart-{{$randomBook->id}} addToCart btn add_to_cart"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                        @endif

                        <button onclick="socialShare('{{$randomBook->slug}}')" class="btn social-share-btn"><i class="fas fa-share-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
