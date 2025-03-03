<!-- books category -->
<section>
    <div class="container">
        <div class="books_category">
            <div class="row">
                <div class="col-8">
                    <h4 class="section_title">
                        New Books
                    </h4>
                </div>
                <div class="col-4 text-end">
                    <a href="{{url('/shop')}}" class="book_category_visit">See More</a>
                </div>
            </div>

            @php
                $newBooks = DB::table('products')
                                        ->leftJoin('users', 'products.author_id', 'users.id')
                                        ->leftJoin('categories', 'products.category_id', 'categories.id')
                                        ->select('products.*', 'users.name as author_name', 'categories.name as category_name')
                                        ->where('products.status', 1)
                                        ->orderBy('id', 'desc')
                                        ->skip(0)
                                        ->limit(10)
                                        ->get();
            @endphp

            <div class="row custom-columns g-4">

                @foreach ($newBooks as $book)
                <div class="col">
                    @include('single_book')
                </div>
                @endforeach

            </div>
        </div>
    </div>
</section>
