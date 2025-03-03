<div class="mb-4">
    <h5 for="filter_sort_by" class="form-label filter_title">Sort By</h5>
    <select class="form-select filter_select" name="filter_sort_by" id="filter_sort_by" onchange="filterProducts()">
        <option value="">Default Sorting</option>
        <option value="1" @if (isset($sort_by) && $sort_by == 1) selected @endif>Sort by Latest</option>
        <option value="2" @if (isset($sort_by) && $sort_by == 2) selected @endif>Price Low to High</option>
        <option value="3" @if (isset($sort_by) && $sort_by == 3) selected @endif>Price High to Low</option>
    </select>
</div>
