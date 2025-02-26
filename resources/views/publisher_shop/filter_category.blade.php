<div class="mb-4">
    <h5 class="form-label filter_title">Book Categories</h5>
    <div class="filter-list">
        @foreach ($categories as $category)
        <div class="filter-item">
            <input type="checkbox" id="{{$category->slug}}" value="{{$category->slug}}" name="filter_category[]" @if(isset($categorySlug) && in_array($category->slug, explode(",", $categorySlug))) checked @endif onchange="filterProducts()">
            <label for="{{$category->slug}}">{{ $category->name }} @if($category->is_audio == 1) (Audio) @endif</label>
            <span class="count">{{DB::table('products')->where('category_id', $category->id)->where('status', 1)->count()}}</span>
        </div>
        @endforeach
    </div>
</div>
