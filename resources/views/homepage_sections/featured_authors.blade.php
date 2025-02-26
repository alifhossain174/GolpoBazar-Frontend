<!-- authors of the month -->
<section>
    <div class="container">
        <div class="author_section">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="author_section_title text-center">Popular Authors</h4>
                </div>
            </div>
            <div class="carousel slide mb-6" id="bookAuthorCarousel">
                <!-- data-bs-ride="carousel" -->
                <!-- Indicators -->
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#bookAuthorCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#bookAuthorCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                </div>

                <div class="carousel-inner">
                    <!-- Slide 1 -->
                    <div class="carousel-item active">
                        <div class="row">
                            @foreach ($bookAuthors as $bookAuthorIndex => $bookAuthor)
                                @if($bookAuthorIndex <= 3)
                                <div class="col-md-3">
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
                                            <p class="author_bio" style="height: 125px;">

                                                @php
                                                    $bookAuthorFullBio = strip_tags($bookAuthor->bio);
                                                    $authorBio = (mb_strlen($bookAuthorFullBio, 'UTF-8') > 200) ? mb_substr($bookAuthorFullBio, 0, 200, 'UTF-8') . "..." : $bookAuthorFullBio;
                                                @endphp

                                                @if($authorBio)
                                                    {{$authorBio}}
                                                @else
                                                    N/A
                                                @endif

                                            </p>
                                            <span class="d-block total_books">Total Books: {{DB::table('products')->where('author_id', $bookAuthor->id)->count()}}</span>
                                            <a href="#" class="d-inline-block author_books">View All books</a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <!-- Slide 2 -->
                    <div class="carousel-item">
                        <div class="row">
                            @foreach ($bookAuthors as $bookAuthorIndex => $bookAuthor)
                                @if($bookAuthorIndex > 3)
                                <div class="col-md-3">
                                    <div class="author_card">
                                        <div class="author_image">
                                            @if($bookAuthor->image)
                                                <img class="lazy" src="{{ url('assets') }}/images/product-load.gif"
                                                data-src="{{ url(env('ADMIN_URL') . '/' . $bookAuthor->image) }}" alt="">
                                            @else
                                                <img src="{{ url('assets') }}/images/authors/author.png" alt="">
                                            @endif
                                        </div>
                                        <div class="author_content">
                                            <h5 class="author_name">{{$bookAuthor->name}}</h5>
                                            <p class="author_bio" style="height: 125px;">

                                                @php
                                                    $bookAuthorFullBio = strip_tags($bookAuthor->bio);
                                                    $authorBio = (mb_strlen($bookAuthorFullBio, 'UTF-8') > 200) ? mb_substr($bookAuthorFullBio, 0, 200, 'UTF-8') . "..." : $bookAuthorFullBio;
                                                @endphp

                                                @if($authorBio)
                                                    {{$authorBio}}
                                                @else
                                                    N/A
                                                @endif

                                            </p>
                                            <span class="d-block total_books">Total Books: {{DB::table('products')->where('author_id', $bookAuthor->id)->count()}}</span>
                                            <a href="#" class="d-inline-block author_books">View All books</a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#bookAuthorCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#bookAuthorCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>

    </div>
</section>
