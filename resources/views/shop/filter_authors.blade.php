<div class="mb-4">
    <h5 class="form-label filter_title">Book Autors</h5>
    <div class="filter-list">
        @foreach ($bookAuthors as $bookAuthor)
        <div class="filter-item">
            <input type="checkbox" id="{{$bookAuthor->id}}" value="{{$bookAuthor->id}}" name="filter_author[]" @if(isset($authorSlug) && in_array($bookAuthor->id, explode(",", $authorSlug))) checked @endif onchange="filterProducts()">
            <label for="{{$bookAuthor->id}}">{{$bookAuthor->name}}</label>
            <span class="count">{{DB::table('products')->where('author_id', $bookAuthor->id)->where('status', 1)->count()}}</span>
        </div>
        @endforeach
    </div>
</div>
