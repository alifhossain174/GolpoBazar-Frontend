<nav class="navbar navbar-expand-md fixed-top" style="background-color: white">
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
                    <a class="nav-link @if(Request::path() == '/') active @endif" href="{{ url('/') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(Request::path() == 'shop') active @endif" href="{{url('/shop')}}">Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(Request::path() == 'audio/books') active @endif" href="{{url('/audio/books')}}">Audio Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(Request::path() == 'authors') active @endif" href="{{url('/authors')}}">Authors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(Request::path() == 'publishers') active @endif" href="{{url('/publishers')}}">Publishers</a>
                </li>

                {{-- @guest
                <li class="nav-item">
                    <a class="nav-link @if(Request::path() == 'login') active @endif" href="{{url('/login')}}">Login</a>
                </li>
                @else
                <li class="nav-item">
                    <a class="nav-link @if(Request::path() == 'home') active @endif" href="{{url('/home')}}">My Account</a>
                </li>
                @endguest --}}
            </ul>

            <form class="d-flex" role="search" style="position: relative">
                <input class="form-control me-2" id="live_search" type="search" onkeyup="liveSearchProduct()" placeholder="Search for Books/Authors" aria-label="Search">
                {{-- dont remove this html; this is for live search --}}
                <ul class="live_search_box d-none">

                </ul>
            </form>

            <div class="dropdown desktopScreenOnlyCartBtn">
                <button class="btn ms-2 dropdown cart_btn dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="cartButton">
                    <strong class="cart-count">{{ session('cart') ? count(session('cart')) : 0 }}</strong> <i class="fas fa-cart-plus"></i>
                </button>
                <ul class="dropdown-menu" id="sidebar_cart" data-bs-popper="static">
                    @include('sidebar_cart')
                </ul>
            </div>

        </div>

        {{-- only show in mobile screen --}}
        <div class="dropdown mobileScreenOnlyCartBtn" style="display:none; position: absolute; top: 7px; right: 90px;">
            <button class="btn ms-2 dropdown cart_btn dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="cartButton">
                <strong class="cart-count">{{ session('cart') ? count(session('cart')) : 0 }}</strong> <i class="fas fa-cart-plus"></i>
            </button>
            <ul class="dropdown-menu" id="sidebar_cart" data-bs-popper="static">
                @include('sidebar_cart')
            </ul>
        </div>

        <style>
            /* Show mobile div only on mobile screens */
            @media (max-width: 768px) {
                .mobileScreenOnlyCartBtn {
                    display: block !important;
                }
                .desktopScreenOnlyCartBtn {
                    display: none !important;
                }
            }

            /* Show desktop div only on desktop screens */
            @media (min-width: 769px) {
                .mobileScreenOnlyCartBtn {
                    display: none !important;
                }
                .desktopScreenOnlyCartBtn {
                    display: block !important;
                }
            }
        </style>

    </div>
</nav>
