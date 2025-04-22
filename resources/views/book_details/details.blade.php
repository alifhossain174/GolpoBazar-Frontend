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
    <title>{{$book->name}} - {{$book->author_name}} - Golpo Bazar - গল্প বাজার</title>
    @if($generalInfo && $generalInfo->fav_icon)<link rel="icon" href="{{env('ADMIN_URL')."/".($generalInfo->fav_icon)}}" type="image/x-icon"/>@endif

    @php
        $bookFullDesForOg = strip_tags($book->description);
        $bookShortOgDesc = (mb_strlen($bookFullDesForOg, 'UTF-8') > 100) ? mb_substr($bookFullDesForOg, 0, 100, 'UTF-8') . "..." : $bookFullDesForOg;
    @endphp

    {{-- open graph meta --}}
    <meta property="og:title" content="@if($book->meta_title){{$book->meta_title}}@else{{$book->name}} - {{$book->author_name}}@endif"/>
    <meta property="og:type" content="book"/>
    <meta property="og:url" content="{{env('APP_URL')."/book/".$book->slug}}"/>
    <meta property="og:image" content="{{env('ADMIN_URL')."/".$book->image}}"/>
    <meta property="og:image:secure_url" content="{{env('ADMIN_URL')."/".$book->image}}"/>
    <meta property="og:image:type" content="image/webp"/>
    <meta property="og:image:width" content="1200"/>
    <meta property="og:image:height" content="630"/>
    <meta property="og:site_name" content="{{$generalInfo ? $generalInfo->company_name : env('APP_NAME')}}"/>
    <meta property="og:description" content="{{$bookShortOgDesc}}"/>
    <meta property="og:locale" content="bn_BD"/>
    <meta property="og:locale:alternate" content="en_US"/>

@endpush

@section('header_css')
    <style>
        .pagination {
            justify-content: left;
        }
        .pagination nav{
            box-shadow: none !important;
            background: transparent !important;
        }
        .publishers .publisher_box .publisher_content h5.publisher_name{
            font-size: 16px;
        }

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
@endsection

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
                            <a href="{{url('/author/books')}}/{{$book->author_id}}" class="author_name d-inline-block">{{$book->author_name}}</a>
                            <p class="mb-0 mt-2">Publisher: <a href="{{url('/publisher/books')}}/{{$book->publisher_slug}}">{{$book->publisher}}</a></p>
                            <p class="mb-0">Publish Date: <span style="font-weight: 500">{{date("jS M Y", strtotime($book->created_at))}}</span></p>

                            @if($book->is_audio == 1)
                            <p class="mb-0">Category: <a href="{{url('audio/books')}}?category={{$book->category_slug}}">{{$book->category_name}}</a></p>
                            @else
                            <p class="mb-0">Category: <a href="{{url('books')}}?category={{$book->category_slug}}">{{$book->category_name}}</a></p>
                            @endif

                            <p class="mb-0">Language: <span style="font-weight: 500">{{$book->language}}</span></p>
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

                            <p class="mb-3">
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

                            @php
                                $bookFinalPrice = 0; //for showing Add to Cart button, beacuse free book will not have cart button
                            @endphp
                            <h5 class="price">
                                @if($book->discount_price && $book->discount_price < $book->price)
                                    @if($book->discount_price == 0)
                                        <span>Free</span>
                                    @else
                                        <small><del>{{number_format($book->price)}}<sup>৳</sup></del></small>
                                        {{number_format($book->discount_price)}}<sup>৳</sup>
                                        @php
                                            $bookFinalPrice = $book->discount_price;
                                        @endphp
                                    @endif
                                @else
                                    @if($book->price == 0)
                                        <span>Free</span>
                                    @else
                                        {{number_format($book->price)}}<sup>৳</sup>
                                        @php
                                            $bookFinalPrice = $book->price;
                                        @endphp
                                    @endif
                                @endif
                            </h5>

                            @php
                                $bookSlug = $book->slug;
                                $bookURLPath = "golpobazar.com/book/{$bookSlug}";
                                $packageName = "app.gstl.golpobazar";
                                $playStoreURL = "https://play.google.com/store/apps/details?id=" . $packageName;
                                $encodedFallbackURL = urlencode($playStoreURL);
                            @endphp

                            <a class="btn btn-sm rounded readBook d-inline-block mb-2" href="intent://{{ $bookURLPath }}#Intent;scheme=https;package={{ $packageName }};S.browser_fallback_url={{ $encodedFallbackURL }};end;"
                            onclick="return handleAppLink(event, '{{ $bookURLPath }}', '{{ $playStoreURL }}');">
                            @if($book->is_audio == 1) <i class="fas fa-volume-up"></i> &nbsp;বইটি শুনুন @else <i class="fas fa-book-open"></i> &nbsp;বইটি পড়ুন @endif
                            </a>
                            @if($bookFinalPrice == 0)
                            <button onclick="socialShare('{{$book->slug}}')" class="btn social-share-btn" style="margin-top: -9px;"><i class="fas fa-share-alt"></i></button>
                            @endif
                            <br>

                            <script>
                                function handleAppLink(event, bookURLPath, fallbackURL) {
                                    if (!/Android/i.test(navigator.userAgent)) {
                                        event.preventDefault();
                                        // For iOS or desktop, open fallback (Play Store or maybe mobile web?)
                                        window.location.href = fallbackURL;
                                        return false;
                                    }
                                    // Allow Android devices to use intent link
                                    return true;
                                }
                            </script>

                            @if($bookFinalPrice != 0)
                                @if (isset(session()->get('cart')[$book->id]))
                                    <button data-id="{{$book->id}}" class="cart-{{$book->id}} removeFromCart btn add_to_cart"><i class="fas fa-times"></i> Remove from Cart</button>
                                @else
                                    <button data-id="{{$book->id}}" class="cart-{{$book->id}} addToCart btn add_to_cart"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                                @endif
                                <button onclick="socialShare('{{$book->slug}}')" class="btn social-share-btn"><i class="fas fa-share-alt"></i></button>
                            @endif

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
