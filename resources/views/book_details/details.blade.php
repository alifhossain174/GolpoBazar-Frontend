@extends('master')

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
                            <a href="authors.html" class="author_name">{{$book->author_name}}</a>
                            <p class="mb-0">Publisher: <a href="publishers.html">{{$book->publisher}}</a></p>
                            <p class="mb-3">Publish Date: <a href="publishers.html">{{date("jS M Y", strtotime($book->created_at))}}</a></p>

                            <p class="mb-0">Category: <a href="books.html">{{$book->category_name}}</a></p>
                            <p class="mb-0">Language: {{$book->language}}</p>
                            <p class="mb-0">Status: @if($book->is_premium == 1) Premium @else Free @endif</p>

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
