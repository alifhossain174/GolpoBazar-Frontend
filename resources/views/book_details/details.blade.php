@extends('master')

@push('site-seo')
    @php
        $generalInfo = DB::table('general_infos')->where('id', 1)->first();
    @endphp

    <meta name="keywords" content="{{$book ? $book->meta_keywords : ''}}" />
    <meta name="description" content="{{$book ? $book->meta_description : ''}}" />
    <meta name="author" content="{{$generalInfo ? $generalInfo->company_name : ''}}">
    <meta name="copyright" content="{{$generalInfo ? $generalInfo->company_name : ''}}">
    <meta name="url" content="{{env('APP_URL')."/book/".$book->slug}}">

    {{-- Page Title & Favicon --}}
    <title>@if($book->meta_title) {{$book->meta_title}} @else {{$book->name}} @endif</title>
    @if($generalInfo && $generalInfo->fav_icon)<link rel="icon" href="{{env('ADMIN_URL')."/".($generalInfo->fav_icon)}}" type="image/x-icon"/>@endif

    {{-- open graph meta --}}
    <meta property="og:title" content="@if($book->meta_title) {{$book->meta_title}} @else {{$book->name}} @endif"/>
    <meta property="og:type" content="{{$book->category_name}}"/>
    <meta property="og:url" content="{{env('APP_URL')."/product/details/".$book->slug}}"/>
    <meta property="og:image" content="{{env('ADMIN_URL')."/".$book->image}}"/>
    <meta property="og:site_name" content="{{$generalInfo ? $generalInfo->company_name : ''}}"/>
    <meta property="og:description" content="{{$book->short_description}}"/>
@endpush

@section('content')

    <section>
        <div class="book_details">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <img class="img-fluid book_cover_image lazy" src="{{ url('assets') }}/images/product-load.gif"
                        data-src="{{ url(env('ADMIN_URL') . '/' . $book->image) }}" alt="">
                    </div>
                    <div class="col-md-4">
                        <div class="book_details_content">
                            <h2 class="book_name">{{$book->name}}</h2>
                            <a href="{{url('/author/books')}}/{{$book->author_id}}" class="author_name">{{$book->author_name}}</a>
                            <p class="mb-0">Publisher: <a href="{{url('/publisher/books')}}/{{$book->publisher_slug}}">{{$book->publisher}}</a></p>
                            <p class="mb-3">Publish Date: <a href="javascript:void(0)">{{date("jS M Y", strtotime($book->created_at))}}</a></p>

                            @if($book->is_audio == 1)
                            <p class="mb-0">Category: <a href="{{env('APP_URL')}}/audio/books?category={{$book->category_slug}}">{{$book->category_name}}</a></p>
                            @else
                            <p class="mb-0">Category: <a href="{{env('APP_URL')}}/shop?category={{$book->category_slug}}">{{$book->category_name}}</a></p>
                            @endif

                            <p class="mb-0">Language: {{$book->language}}</p>
                            <p class="mb-0">Type: @if($book->is_audio == 1) Audio Book @else Ebook @endif</p>

                            @php
                                $productReviews = DB::table('product_reviews')
                                                    ->leftJoin('users', 'product_reviews.user_id', 'users.id')
                                                    ->select('product_reviews.*', 'users.name as user_name', 'users.image as user_image')
                                                    ->where('product_reviews.product_id', $book->id)
                                                    ->orderBy('product_reviews.id', 'desc')
                                                    ->get();

                                $productRating = DB::table('product_reviews')->where('product_id', $book->id)->sum('rating');
                            @endphp

                            <p class="mb-5">
                                Rating ({{count($productReviews)}}):
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
                            </p>

                            <h5 class="price">
                                @if($book->discount_price && $book->discount_price < $book->price)
                                    <small><del>{{number_format($book->price)}}<sup>৳</sup></del></small>
                                    {{number_format($book->discount_price)}}<sup>৳</sup>
                                @else
                                    {{number_format($book->price)}}<sup>৳</sup>
                                @endif
                            </h5>

                            @php
                                // $bookURL = env('APP_URL')."/book/".$book->slug;
                                $bookURL = "https://golpobazar.com/book/".$book->slug;
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

                            <a class="btn btn-sm rounded readBook" href="intent://{{ $bookURL }}#Intent;scheme=https;package={{ $packageName }};S.browser_fallback_url={{ $encodedFallbackURL }};end;"
                            onclick="return handleAppLink(event, '{{ $bookURL }}', '{{ $playStoreURL }}');">
                            <i class="fas fa-book-open"></i> &nbsp;বইটি পড়ুন
                            </a>

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

                            @if (isset(session()->get('cart')[$book->id]))
                            <button data-id="{{$book->id}}" class="cart-{{$book->id}} removeFromCart btn add_to_cart"><i class="fas fa-times"></i> Remove from Cart</button>
                            @else
                            <button data-id="{{$book->id}}" class="cart-{{$book->id}} addToCart btn add_to_cart"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                            @endif

                            <button onclick="socialShare('{{$book->slug}}')" class="btn social-share-btn"><i class="fas fa-share-alt"></i></button>
                        </div>

                    </div>
                    <div class="col-lg-5">
                        <div class="book_description">
                            <h4>About this book</h4>
                            <p>
                                @php
                                    $bookFullDes = strip_tags($book->description);
                                    $bookDescription = (mb_strlen($bookFullDes, 'UTF-8') > 500) ? mb_substr($bookFullDes, 0, 500, 'UTF-8') . "..." : $bookFullDes;
                                @endphp
                                {{$bookDescription}}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('book_details.reviews')
    @include('book_details.related_books')

@endsection


