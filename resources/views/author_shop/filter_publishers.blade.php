<div class="mb-4">
    <h5 class="form-label filter_title">Book Publishers</h5>
    <div class="filter-list">

        @foreach ($publishers as $publisher)
        <div class="filter-item">
            <input type="checkbox" id="{{$publisher->slug}}" value="{{$publisher->slug}}" name="filter_publisher[]" @if(isset($publisherSlug) && in_array($publisher->slug, explode(",", $publisherSlug))) checked @endif onchange="filterProducts()">
            <label for="{{$publisher->slug}}">{{$publisher->name}}</label>
            <span class="count">{{DB::table('products')->where('author_id', $authorInfo->id)->where('brand_id', $publisher->id)->where('status', 1)->count()}}</span>
        </div>
        @endforeach

    </div>
</div>
