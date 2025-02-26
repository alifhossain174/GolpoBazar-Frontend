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

    @yield('header_css')

</head>
<body>

    <!-- header -->
    <nav class="navbar navbar-expand-md fixed-top bg-white">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ url(env('ADMIN_URL') . '/' . $generalInfo->logo) }}" alt="{{ $generalInfo->company_name }}" width="50" height="50" class="d-inline-block align-text-top">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('/shop')}}">Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="authors.html">Authors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="publishers.html">Publishers</a>
                    </li>
                </ul>

                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search for books here" aria-label="Search">
                </form>

                <div class="dropdown">
                    <button class="btn ms-2 dropdown cart_btn dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="cartButton">
                        <strong class="cart-count">3</strong> <i class="fas fa-cart-plus"></i>
                    </button>
                    <ul class="dropdown-menu" data-bs-popper="static">
                        <li><a class="dropdown-item" href="#">1 * Book Name-1</a></li>
                        <li><a class="dropdown-item" href="#">2 * Book Name-2</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="text-center">
                            <a class="btn btn-sm rounded d-inline-block checkout_btn" href="checkout.html"><i class="far fa-share-square"></i> Checkout</a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </nav>


    @yield('content')


    <!-- footer -->
    <section>
        <div class="footer">
            <div class="container">
                <footer class="d-flex flex-wrap justify-content-between align-items-center py-3">
                    <div class="col-md-4 d-flex align-items-center">
                        <a href="{{url('/')}}" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1">
                            <img src="{{ url(env('ADMIN_URL') . '/' . $generalInfo->logo) }}" alt="{{ $generalInfo->company_name }}" width="45" height="45" class="d-inline-block align-text-top">
                        </a>
                        <span class="mb-3 mb-md-0 copyright_text">{{$generalInfo->footer_copyright_text}}</span>
                    </div>

                    <ul class="nav col-md-4 justify-content-end list-unstyled d-flex">

                        @if($generalInfo->facebook)
                        <li class="ms-4"><a class="footer_icon" href="{{$generalInfo->facebook}}"><i class="fab fa-facebook-square"></i></a></li>
                        @endif

                        @if($generalInfo->twitter)
                        <li class="ms-4"><a class="footer_icon" href="{{$generalInfo->twitter}}"><i class="fab fa-twitter"></i></a></li>
                        @endif

                        @if($generalInfo->instagram)
                        <li class="ms-4"><a class="footer_icon" href="{{$generalInfo->instagram}}"><i class="fab fa-instagram"></i></a></li>
                        @endif

                        @if($generalInfo->linkedin)
                        <li class="ms-4"><a class="footer_icon" href="{{$generalInfo->linkedin}}"><i class="fab fa-linkedin"></i></a></li>
                        @endif

                        @if($generalInfo->telegram)
                        <li class="ms-4"><a class="footer_icon" href="{{$generalInfo->telegram}}"><i class="fab fa-telegram"></i></a></li>
                        @endif

                        @if($generalInfo->youtube)
                        <li class="ms-4"><a class="footer_icon" href="{{$generalInfo->youtube}}"><i class="fab fa-youtube"></i></a></li>
                        @endif

                    </ul>
                </footer>
            </div>
        </div>
    </section>

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

        function addToCart() {
            alert('Added to cart');
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
