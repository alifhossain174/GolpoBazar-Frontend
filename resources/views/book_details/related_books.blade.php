<section>
    <div class="container">
        <div class="books_category">
            <div class="row">
                <div class="col-8">
                    <h4 class="section_title">Similar Books</h4>
                </div>
                <div class="col-4 text-end">
                    <a href="{{url('/shop')}}" class="book_category_visit">See More</a>
                </div>
            </div>

            <div class="row custom-columns g-4">
                @foreach ($relatedBooks as $book)
                    <div class="col">
                        @include('single_book')
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
