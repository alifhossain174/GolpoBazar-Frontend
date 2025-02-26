<section>
    <div class="book_reviews_section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="section_title">Book Reviews <span>({{count($productReviews)}})</span></h4>
                </div>
            </div>

            <div class="row">
                @foreach ($productReviews as $productReview)
                <div class="col-lg-12">
                    <div class="review_box">

                        @if($productReview->user_image)
                            <img class="user_image lazy" style="min-width: 80px; max-height: 80px; border-radius: 50%;" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url(env('ADMIN_URL') . '/' . $productReview->user_image) }}" alt="">
                        @else
                            <img class="user_image lazy" style="min-width: 80px; max-height: 80px; border-radius: 50%;" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url('assets') }}/images/authors/author.png" alt="">
                        @endif

                        <div class="review_content w-100">
                            <h4 class="reviewer_name">{{$productReview->user_name}}</h4>
                            <p class="review_date">{{date("jS F, Y", strtotime($productReview->created_at))}}</p>
                            <div class="d-flex align-items-center mb-2">
                                <div class="book_rating">

                                    @for ($i=1;$i<=$productReview->rating;$i++)
                                    <i class="fas fa-star rating"></i>
                                    @endfor

                                    @for ($i=1;$i<=5-$productReview->rating;$i++)
                                    <i class="far fa-star rating"></i>
                                    @endfor

                                </div>
                            </div>
                            <p class="review">
                                {{$productReview->review}}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- <div class="row">
                <div class="col-12">
                    <button class="btn load_more_reviews">Load More Reviews</button>
                </div>
            </div> --}}

        </div>
    </div>
</section>
