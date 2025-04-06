<!-- books category -->
<section>
    <div class="container">
        <div class="books_category">
            <div class="row">
                <div class="col-8">
                    <h4 class="section_title">
                        {{$category->name}}

                        @if($category->is_audio == 1)
                            (Audio)
                        @else
                            (Ebook)
                        @endif
                    </h4>
                </div>
                <div class="col-4 text-end">
                    @if($category->is_audio == 1)
                    <a href="{{url('audio/books')}}?category={{$category->slug}}" class="book_category_visit">See More</a>
                    @else
                    <a href="{{url('books')}}?category={{$category->slug}}" class="book_category_visit">See More</a>
                    @endif
                </div>
            </div>

            @php
                $categoryWiseBooks = DB::table('products')
                                        ->leftJoin('users', 'products.author_id', 'users.id')
                                        ->leftJoin('categories', 'products.category_id', 'categories.id')
                                        ->select('products.*', 'users.name as author_name', 'categories.name as category_name')
                                        ->where('products.category_id', $category->id)
                                        ->where('products.status', 1)
                                        ->orderBy('id', 'desc')
                                        ->skip(0)
                                        ->limit(10)
                                        ->get();
            @endphp

            <div class="row custom-columns g-4">

                @foreach ($categoryWiseBooks as $book)
                <div class="col">
                    @include('single_book')
                </div>
                @endforeach

            </div>
        </div>
    </div>
</section>
