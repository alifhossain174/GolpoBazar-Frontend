<!-- authors of the month -->
<section>
    <div class="container">
        <div class="author_section">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="author_section_title text-center">Popular Authors</h4>
                </div>
            </div>


            <div class="row">
                <div class="col-lg-12">
                    <div class="swiper-container">
                        <!-- Swiper Wrapper -->
                        <div class="swiper-wrapper">
                            <!-- Each swiper-slide represents a single slide -->
                            @foreach ($bookAuthors as $bookAuthor)
                            <div class="swiper-slide">
                                <div class="author_card">
                                    <div class="author_image">
                                        @if($bookAuthor->image)
                                            <img class="lazy" src="{{ url('assets') }}/images/product-load.gif"
                                            data-src="{{ url(env('ADMIN_URL') . '/' . $bookAuthor->image) }}" alt="">
                                        @else
                                            <img class="lazy" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url('assets') }}/images/authors/author.png" alt="">
                                        @endif
                                    </div>
                                    <div class="author_content">
                                        <h5 class="author_name">{{$bookAuthor->name}}</h5>
                                        <span class="d-block total_books">Total Books: {{DB::table('products')->where('author_id', $bookAuthor->id)->count()}}</span>
                                        <a href="{{url('author/books')}}/{{$bookAuthor->id}}" class="d-inline-block author_books">View All books</a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Add Pagination (optional) -->
                        <div class="swiper-pagination"></div>

                        <!-- Add Navigation (optional) -->
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
