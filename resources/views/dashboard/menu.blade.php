<!-- Left sidebar with Bootstrap nav tabs -->
<div class="col-md-3 col-lg-2 p-0 sidebar">
    <div class="user-profile">

        @if(Auth::user()->image && file_exists(public_path('uploads/users/'.Auth::user()->image)))
            <img src="{{url('uploads/users')}}/{{Auth::user()->image}}" class="rounded-circle user-avatar mb-3" alt="Profile Image">
        @else
            <img src="{{url('assets')}}/images/authors/author.png" class="rounded-circle user-avatar mb-3" alt="Profile Image">
        @endif

        <h5 class="mb-1">{{Auth::user()->name}}</h5>
        <p class="text-muted small mb-0">{{Auth::user()->phone}}</p>
    </div>

    <div class="nav flex-column nav-pills">
        <a onclick="changeURL('/home')" class="nav-link @if(Request::path() == 'home') show active @endif" id="dashboard-tab" data-bs-toggle="pill" href="#dashboard">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a class="nav-link" id="orders-tab" data-bs-toggle="pill" href="#orders">
            <i class="bi bi-bag me-2"></i> Orders
        </a>
        <a onclick="changeURL('/user/cart')" class="nav-link @if(Request::path() == 'user/cart') active @endif" id="cart-tab" data-bs-toggle="pill" href="#cart">
            <i class="bi bi-cart me-2"></i> Cart
        </a>
        <a onclick="changeURL('/user/profile')" class="nav-link @if(Request::path() == 'user/profile') active @endif" id="profile-tab" data-bs-toggle="pill" href="#profile">
            <i class="bi bi-person me-2"></i> Profile
        </a>
        <a onclick="changeURL('/change/password')" class="nav-link @if(Request::path() == 'change/password') active @endif" id="password-tab" data-bs-toggle="pill" href="#password">
            <i class="bi bi-key me-2"></i> Change Password
        </a>
        <a class="nav-link text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</div>
