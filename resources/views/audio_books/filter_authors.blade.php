<div class="mb-4">
    <h5 class="form-label filter_title">Book Autors</h5>
    <div class="filter-list">
        @foreach ($bookAuthors as $bookAuthor)
            @php
                $totalBooksOfAuthor = DB::table('products')->where('author_id', $bookAuthor->id)->where('is_audio', 1)->where('status', 1)->count();
            @endphp
            @if($totalBooksOfAuthor > 0)
            <div class="filter-item">
                <input type="checkbox" id="{{$bookAuthor->id}}" value="{{$bookAuthor->id}}" name="filter_author[]" @if(isset($authorSlug) && in_array($bookAuthor->id, explode(",", $authorSlug))) checked @endif onchange="filterProducts()">
                <label for="{{$bookAuthor->id}}">{{$bookAuthor->name}}</label>
                <span class="count">{{$totalBooksOfAuthor}}</span>
            </div>
            @endif
        @endforeach
    </div>
</div>
