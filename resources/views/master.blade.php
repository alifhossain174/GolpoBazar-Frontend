<!doctype html>
<html lang="en">

@php
    $generalInfo = DB::table('general_infos')->where('id', 1)->first();
@endphp

<head>

    <!-- Start Meta Data -->
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- End Meta Data -->

    @stack('site-seo')

    <link rel="stylesheet" href="{{ url('assets') }}/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ url('assets') }}/css/toastr.min.css">
    <link rel="stylesheet" href="{{ url('assets') }}/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ url('assets') }}/css/style_v2.css">
    <link rel="stylesheet" href="{{ url('assets') }}/css/responsive.css">

    <style>
        button.removeFromCart {
            background-color: #de0000 !important;
        }

        /* live search css start */
        ul.live_search_box {
            position: absolute;
            top: 95%;
            left: 0px;
            z-index: 999;
            background: white;
            border: 1px solid lightgray;
            width: 100%;
            padding: 0px;
            border-radius: 0px 0px 4px 4px;
        }

        ul.live_search_box li.live_search_item {
            list-style: none;
            border-bottom: 1px solid lightgray;
        }

        ul.live_search_box li.live_search_item:last-child {
            border-bottom: none;
        }

        ul.live_search_box li.live_search_item a.live_search_product_link {
            display: flex;
            padding: 10px;
            transition: all .1s linear;
        }

        ul.live_search_box li.live_search_item a.live_search_product_link:hover {
            box-shadow: 1px 1px 5px #cecece inset;
        }

        ul.live_search_box li.live_search_item a.live_search_product_link img.live_search_product_image {
            width: 45px;
            height: 70px;
            min-width: 45px;
            min-height: 70px;
            border: 1px solid lightgray;
            border-radius: 4px
        }

        ul.live_search_box li.live_search_item a.live_search_product_link h6.live_search_product_title {
            margin-left: 4px;
            margin-top: 2px;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 148px;
            font-size: 14px;
            color: #1e1e1e;
            font-weight: 600;
        }

        ul.live_search_box li.live_search_item a.live_search_product_link h5.live_search_book_author {
            margin-left: 6px;
            margin-top: 0px;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 150px;
            font-size: 12px;
            color: gray;
            font-weight: 500;
        }

        ul.live_search_box li.live_search_item a.live_search_product_link span.live_search_product_price {
            display: block;
            margin-left: 6px;
            margin-top: 2px;
            color: #20B1B6;
            font-size: 18px;
            font-weight: 600;
        }

        ul.live_search_box li.live_search_item a.live_search_product_link .book_details span.bookTag{
            position: absolute;
            bottom: 0;
            right: 0;
            border: 1px solid #cacaca;
            padding: 0px 8px;
            border-radius: 4px;
            font-size: 12px;
            line-height: 17px;
            background:#eee;
            color: gray;
        }

        /* live search css end */

        ul#sidebar_cart {
            left: auto !important;
            right: 0 !important;
        }
    </style>

    @yield('header_css')

</head>

<body>

    @include('header')

    @yield('content')

    @include('footer')


    <script src="{{ url('assets') }}/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('assets') }}/js/jquery-3.7.1.min.js"></script>

    <script src="{{ url('assets') }}/js/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper('.swiper-container', {
            spaceBetween: 20, // Space between slides
            loop: true, // Infinite loop of slides
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                1200: { // For screens 1200px or larger
                    slidesPerView: 4,
                },
                768: { // For screens 768px to 1199px
                    slidesPerView: 3,
                },
                480: { // For screens smaller than 768px
                    slidesPerView: 1,
                }
            }
        });
    </script>

    <script>
        function renderLazyImage() {
            var lazyloadImages;
            if ("IntersectionObserver" in window) {
                lazyloadImages = document.querySelectorAll(".lazy");
                var imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var image = entry.target;
                            image.src = image.dataset.src;
                            image.classList.remove("lazy");
                            imageObserver.unobserve(image);
                        }
                    });
                });

                lazyloadImages.forEach(function(image) {
                    imageObserver.observe(image);
                });
            } else {
                var lazyloadThrottleTimeout;
                lazyloadImages = document.querySelectorAll(".lazy");

                function lazyload() {
                    if (lazyloadThrottleTimeout) {
                        clearTimeout(lazyloadThrottleTimeout);
                    }

                    lazyloadThrottleTimeout = setTimeout(function() {
                        var scrollTop = window.pageYOffset;
                        lazyloadImages.forEach(function(img) {
                            if (img.offsetTop < (window.innerHeight + scrollTop)) {
                                img.src = img.dataset.src;
                                img.classList.remove('lazy');
                            }
                        });
                        if (lazyloadImages.length == 0) {
                            document.removeEventListener("scroll", lazyload);
                            window.removeEventListener("resize", lazyload);
                            window.removeEventListener("orientationChange", lazyload);
                        }
                    }, 20);
                }

                document.addEventListener("scroll", lazyload);
                window.addEventListener("resize", lazyload);
                window.addEventListener("orientationChange", lazyload);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            renderLazyImage();
        })

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        function liveSearchProduct() {

            var searchKeyword = $("#live_search").val();

            if (searchKeyword && searchKeyword != '' && searchKeyword != null) {
                var formData = new FormData();
                formData.append("search_keyword", searchKeyword);

                $.ajax({
                    data: formData,
                    url: "{{ url('product/live/search') }}",
                    type: "POST",
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        $('.live_search_box').removeClass('d-none');
                        $('.live_search_box').html(data.searchResults);
                        renderLazyImage();
                    },
                    error: function(data) {
                        toastr.options.positionClass = 'toast-bottom-right';
                        toastr.options.timeOut = 1000;
                        toastr.error("Something Went Wrong");
                    }
                });
            } else {
                $('.live_search_box').addClass('d-none');
            }

        }

        $('body').on('click', '.addToCart', function() {
            var id = $(this).data('id');
            $.get("{{ url('add/to/cart') }}" + '/' + id, function(data) {
                toastr.options.positionClass = 'toast-bottom-right';
                toastr.options.timeOut = 1000;
                toastr.success("Added to Cart");
                $("#sidebar_cart").html(data.rendered_cart);
                $("strong.cart-count").html(data.cartTotalQty);
            })

            if ($(this).hasClass('add_to_cart')) {
                $(this).html("<i class='fas fa-times'></i> Remove from Cart");
            } else {
                $(this).html("<i class='fas fa-times'></i>");
            }

            $(this).removeClass("addToCart");
            $(this).addClass("removeFromCart");
            $(this).blur();
        });

        $('body').on('click', '.removeFromCart', function() {
            var id = $(this).data('id');
            $.get("{{ url('remove/cart/item') }}" + '/' + id, function(data) {
                toastr.options.positionClass = 'toast-bottom-right';
                toastr.options.timeOut = 1000;
                toastr.error("Removed from Cart");
                $("strong.cart-count").html(data.cartTotalQty);
                $("span.view_cart_count").html(data.cartTotalQty);
                $("#sidebar_cart").html(data.rendered_cart);
                $("#view_cart_items").html(data.viewCartItems);
                renderLazyImage();
            })

            $('.cart-' + id).html("<i class='fas fa-cart-plus'></i>");
            $('.cart-' + id).attr('data-id', id).removeClass("removeFromCart");
            $('.cart-' + id).attr('data-id', id).addClass("addToCart");
            $('.cart-' + id).blur();

            if ($(this).hasClass('add_to_cart')) {
                $(this).html("<i class='fas fa-cart-plus'></i> Add to Cart");
            }
        });

        function addToCart() {
            toastr.success("Added to Cart");
        }

        function socialShare(url) {
            navigator.clipboard.writeText("{{ env('APP_URL') }}/book/" + url);
            toastr.success("Book Link Copied");
        }
        function authorSocialShare(url) {
            navigator.clipboard.writeText("{{ env('APP_URL') }}/author/books/" + url);
            toastr.success("Author's Link Copied");
        }
    </script>


    @yield('footer_js')

    <script src="{{ url('assets') }}/js/toastr.min.js"></script>
    {!! Toastr::message() !!}

</body>

</html>
