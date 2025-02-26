@extends('master')

@section('header_css')
    <style>
        .pagination nav{
            box-shadow: none !important;
            background: transparent !important;
        }
    </style>
@endsection

@section('content')

<section>
    <div class="publisher_author_wise_books">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="publisher_author_box">

                        @if($authorInfo->image)
                            <img class="publisher_author_image lazy" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url(env('ADMIN_URL') . '/' . $authorInfo->image) }}" alt="">
                        @else
                            <img class="publisher_author_image lazy" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url('assets') }}/images/authors/author.png" alt="">
                        @endif

                        <div class="publisher_author_content w-100">
                            <h3>{{$authorInfo->name}}</h3>
                            <p>{{$authorInfo->bio}}</p>
                            <span>Total Books: {{DB::table('products')->where('author_id', $authorInfo->id)->where('status', 1)->count()}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="book_shop">
        <div class="container">
            <div class="row">
                <div class="col-md-3" id="filterSidebar">
                    <button class="btn d-md-none mb-3 showHideFilterBtn" id="hideFilterBtn"><i
                            class="fas fa-eye-slash"></i> Hide Filters</button>
                    <div class="filter_sidebar">

                        <div class="mb-3">
                            <input type="text" id="search_keyword" class="form-control filter_search" onkeyup="filterProducts()" placeholder="Search by Book Name">
                        </div>

                        @include('author_shop.filter_sorting')
                        @include('author_shop.filter_category')
                        @include('author_shop.filter_publishers')

                    </div>
                </div>
                <div class="col-md-9">
                    <button class="btn d-md-none mb-3 showHideFilterBtn" id="toggleFilterBtn"><i class="fas fa-filter"></i> Show Filters</button>

                    <div class="row">
                        <div class="col-lg-12" id="product_wrapper">
                            @include('author_shop.products')
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="filter_overlay" id="overlay"></div>

    </div>
</section>

@endsection

@section('footer_js')
    <script>
        const toggleFilterBtn = document.getElementById('toggleFilterBtn');
        const filterSidebar = document.getElementById('filterSidebar');
        const overlay = document.getElementById('overlay');
        const hideFilterBtn = document.getElementById('hideFilterBtn');

        toggleFilterBtn.addEventListener('click', () => {
            filterSidebar.classList.toggle('show');
            overlay.classList.toggle('show');

            const sourceContent = filterSidebar.innerHTML;
            overlay.innerHTML = sourceContent;
        });

        // Event delegation: Listen for click events on the parent
        overlay.addEventListener('click', function (event) {
            if (event.target && event.target.matches('#hideFilterBtn')) {
                filterSidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });


        function filterProducts() {

            // fetching filter values
            let category_array = [];
            let publisher_array = [];

            $("input[name='filter_category[]']").each(function() {
                if ($(this).is(':checked')) {
                    if (!category_array.includes($(this).val())) {
                        category_array.push($(this).val());
                    }
                }
            });
            $("input[name='filter_publisher[]']").each(function() {
                if ($(this).is(':checked')) {
                    if (!publisher_array.includes($(this).val())) {
                        publisher_array.push($(this).val());
                    }
                }
            });

            let category_slugs = String(category_array);
            let publisher_slugs = String(publisher_array);
            var sort_by = Number($("#filter_sort_by").val());
            var search_keyword = $("#search_keyword").val();


            // setting up get url with filter parameters
            var baseUrl = window.location.pathname;

            if (category_slugs) {
                baseUrl.indexOf('?') !== -1 ? baseUrl += '&category=' + category_slugs : baseUrl += '?category=' + category_slugs;
            }
            if (publisher_slugs) {
                baseUrl.indexOf('?') !== -1 ? baseUrl += '&publisher=' + publisher_slugs : baseUrl += '?publisher=' + publisher_slugs;
            }
            if (sort_by && sort_by > 0) {
                baseUrl.indexOf('?') !== -1 ? baseUrl += '&sort_by=' + sort_by : baseUrl += '?sort_by=' + sort_by;
            }
            if (search_keyword) {
                baseUrl.indexOf('?') !== -1 ? baseUrl += '&search_keyword=' + search_keyword : baseUrl += '?search_keyword=' + search_keyword;
            }
            history.pushState(null, null, baseUrl);


            // sending request
            var formData = new FormData();
            formData.append("category", category_slugs);
            formData.append("author", {{ $authorInfo->id }});
            formData.append("publisher", publisher_slugs);
            formData.append("sort_by", sort_by);
            formData.append("search_keyword", search_keyword);
            formData.append("path_name", window.location.pathname);

            $.ajax({
                data: formData,
                url: "{{ url('filter/author/books') }}",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    $('#product_wrapper').fadeOut(function() {
                        $(this).html(data.rendered_view);
                        $(this).fadeIn();
                        renderLazyImage()
                    });
                },
                error: function(data) {
                    toastr.options.positionClass = 'toast-bottom-right';
                    toastr.options.timeOut = 1000;
                    toastr.error("Something Went Wrong");
                }
            });
        }

    </script>
@endsection
