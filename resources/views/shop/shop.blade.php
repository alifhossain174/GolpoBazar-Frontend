@extends('master')


@push('site-seo')
    @php
        $generalInfo = DB::table('general_infos')->where('id', 1)->first();
    @endphp
    <title>
        @if ($generalInfo && $generalInfo->tab_title)
            {{ $generalInfo->tab_title }}
        @else
            {{ $generalInfo->company_name }}
        @endif
    </title>
    @if ($generalInfo && $generalInfo->fav_icon)
        <link rel="icon" href="{{ env('ADMIN_URL') . '/' . $generalInfo->fav_icon }}" type="image/x-icon" />
    @endif
@endpush


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

                        @include('shop.filter_sorting')
                        @include('shop.filter_category')
                        @include('shop.filter_authors')
                        @include('shop.filter_publishers')

                    </div>
                </div>
                <div class="col-md-9">
                    <button class="btn d-md-none mb-3 showHideFilterBtn" id="toggleFilterBtn"><i class="fas fa-filter"></i> Show Filters</button>

                    <div class="row">
                        <div class="col-lg-12" id="product_wrapper">
                            @include('shop.products')
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
            let author_array = [];
            let publisher_array = [];

            $("input[name='filter_category[]']").each(function() {
                if ($(this).is(':checked')) {
                    if (!category_array.includes($(this).val())) {
                        category_array.push($(this).val());
                    }
                }
            });
            $("input[name='filter_author[]']").each(function() {
                if ($(this).is(':checked')) {
                    if (!author_array.includes($(this).val())) {
                        author_array.push($(this).val());
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
            let author_slugs = String(author_array);
            let publisher_slugs = String(publisher_array);
            var sort_by = Number($("#filter_sort_by").val());
            var search_keyword = $("#search_keyword").val();


            // setting up get url with filter parameters
            var baseUrl = window.location.pathname;

            if (category_slugs) {
                baseUrl.indexOf('?') !== -1 ? baseUrl += '&category=' + category_slugs : baseUrl += '?category=' + category_slugs;
            }
            if (author_slugs) {
                baseUrl.indexOf('?') !== -1 ? baseUrl += '&author=' + author_slugs : baseUrl += '?author=' + author_slugs;
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
            formData.append("author", author_slugs);
            formData.append("publisher", publisher_slugs);
            formData.append("sort_by", sort_by);
            formData.append("search_keyword", search_keyword);
            formData.append("path_name", window.location.pathname);

            $.ajax({
                data: formData,
                url: "{{ url('filter/books') }}",
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
