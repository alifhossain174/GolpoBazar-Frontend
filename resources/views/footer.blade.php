<footer class="footer">
    <div class="footer-content">
        <!-- Left Column -->
        <div class="footer-info">
            <a href="{{url('/')}}" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1">
                <img src="{{ url(env('ADMIN_URL') . '/' . $generalInfo->logo) }}" alt="{{ $generalInfo->company_name }}" width="22" height="22" class="d-inline-block align-text-top">
                <h2 class="d-inline-block">{{ $generalInfo->company_name }}</h2>
            </a>

            <p>{{ $generalInfo->short_description }}</p>

            <div class="contact-info">
                @if($generalInfo->contact)<div>{{ $generalInfo->contact }}</div>@endif
                @if($generalInfo->email)<div>{{ $generalInfo->email }}</div>@endif
                <div class="social-link">
                    Follow us on
                </div>

                <ul class="nav justify-content-start list-unstyled d-flex">

                    @if($generalInfo->facebook)
                    <li class="me-3"><a class="footer_icon" href="{{$generalInfo->facebook}}"><i class="fab fa-facebook-square" style="color: #1877F2"></i></a></li>
                    @endif

                    @if($generalInfo->twitter)
                    <li class="me-3"><a class="footer_icon" href="{{$generalInfo->twitter}}"><i class="fab fa-twitter" style="color: #1da1f2"></i></a></li>
                    @endif

                    @if($generalInfo->instagram)
                    <li class="me-3"><a class="footer_icon" href="{{$generalInfo->instagram}}"><i class="fab fa-instagram" style="color: #fccc63"></i></a></li>
                    @endif

                    @if($generalInfo->linkedin)
                    <li class="me-3"><a class="footer_icon" href="{{$generalInfo->linkedin}}"><i class="fab fa-linkedin" style="color: #0077B5"></i></a></li>
                    @endif

                    @if($generalInfo->telegram)
                    <li class="me-3"><a class="footer_icon" href="{{$generalInfo->telegram}}"><i class="fab fa-telegram" style="color: #0088cc"></i></a></li>
                    @endif

                    @if($generalInfo->youtube)
                    <li class="me-3"><a class="footer_icon" href="{{$generalInfo->youtube}}"><i class="fab fa-youtube" style="color: red"></i></a></li>
                    @endif

                </ul>

            </div>
        </div>

        <!-- Middle Column - Navigation -->
        <div class="footer-nav">
            <h4 class="links">Important Links</h4>
            <ul>
                <li><a href="{{ url('terms/and/conditions') }}">Terms & Condition</a></li>
                <li><a href="{{ url('privacy/policy') }}">Privacy Policy</a></li>
                <li><a href="{{ url('shipping/policy') }}">Shipping Policy</a></li>
                <li><a href="{{ url('return/policy') }}">Return Policy</a></li>
                <li><a href="{{url('/login')}}">My Account</a></li>
            </ul>
        </div>

        <!-- Right Column - App Downloads -->
        <div class="app-downloads">
            @if($generalInfo->play_store_link)
            <a href="{{$generalInfo->play_store_link}}" class="app-button">
                <img class="lazy" style="width: 170px; height: 52px;" src="{{ url('assets') }}/images/product-load.gif" data-src="{{url('assets')}}/images/play_store.png" alt="Get it on Google Play">
            </a>
            @endif

            @if($generalInfo->app_store_link)
            <a href="{{$generalInfo->app_store_link}}" class="app-button">
                <img class="lazy" style="width: 170px; height: 52px;" src="{{ url('assets') }}/images/product-load.gif" data-src="{{url('assets')}}/images/app_store.png" alt="Download on the App Store">
            </a>
            @endif

            <div class="copyright">{{$generalInfo->footer_copyright_text}}</div>
        </div>
    </div>
</footer>
