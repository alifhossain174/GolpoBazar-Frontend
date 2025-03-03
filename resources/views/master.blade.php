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

    <link rel="stylesheet" href="{{url('assets')}}/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ url('assets') }}/css/toastr.min.css">
    <link rel="stylesheet" href="{{url('assets')}}/css/style.css">
    <link rel="stylesheet" href="{{url('assets')}}/css/responsive.css">

    <style>
        button.removeFromCart{
            background-color: #de0000 !important;
        }
    </style>

    @yield('header_css')

</head>
<body>

    @include('header')

    @yield('content')

    @include('footer')


    <script src="{{url('assets')}}/js/bootstrap.bundle.min.js"></script>
    <script src="{{url('assets')}}/js/jquery-3.7.1.min.js"></script>
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
            navigator.clipboard.writeText("{{ env('APP_URL') }}/book/"+url);
            toastr.success("Book Link Copied");
        }
    </script>


    @yield('footer_js')

    <script src="{{ url('assets') }}/js/toastr.min.js"></script>
    {!! Toastr::message() !!}

</body>
</html>
