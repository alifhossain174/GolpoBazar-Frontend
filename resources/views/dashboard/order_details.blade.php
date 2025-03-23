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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .nav-pills .nav-link {
            color: #495057;
            border-radius: 0;
            padding: 12px 20px;
            margin-bottom: 5px;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }

        .nav-pills .nav-link:hover:not(.active) {
            background-color: #e9ecef;
        }

        .user-profile {
            text-align: center;
            padding: 20px 10px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .status-badge {
            font-size: 12px;
            padding: 5px 10px;
        }

        .sidebar {
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            height: 100%;
            transition: all 0.3s ease;
        }

        /* Mobile sidebar styles */
        .mobile-sidebar-toggle {
            display: none;
            position: fixed;
            top: 78px;
            left: 10px;
            z-index: 1050;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .mobile-sidebar-toggle:focus {
            outline: none;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.5);
        }

        .order-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 20px;
        }


        .order-header {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .order-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .status-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
        }

        .order-total {
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
            margin-top: 15px;
        }

        .invoice-actions {
            margin-top: 20px;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 25px;
            border-left: 2px solid #dee2e6;
            padding-left: 20px;
        }

        .timeline-item:last-child {
            border-left: 2px solid transparent;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #0d6efd;
        }

        .timeline-item.completed::before {
            background-color: #198754;
        }

        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0px;
        }

        /* Mobile sidebar control */
        #sidebarCollapse {
            display: none;
        }

        .mobile-header {
            display: none;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        /* Media queries for responsiveness */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -280px;
                width: 280px;
                height: 100vh;
                z-index: 1040;
                overflow-y: auto;
            }

            .sidebar.active {
                left: 0;
            }

            .mobile-sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            #sidebarCollapse {
                display: block;
            }

            .mobile-header {
                display: block;
            }

            .main-content {
                margin-left: 0 !important;
                width: 100%;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1030;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        @media (max-width: 767.98px) {
            .product-img {
                width: 60px;
                height: 60px;
            }

            .order-section {
                padding: 15px;
            }

            .mobile-header .btn {
                padding: 5px 10px;
                font-size: 0.85rem;
            }
        }

        @media print {
            .sidebar, .no-print {
                display: none !important;
            }

            .navbar {
                display: none !important;
            }

            footer {
                display: none !important;
            }

            .col-md-9 {
                width: 100%;
                flex: 0 0 100%;
                max-width: 100%;
            }

            .order-section {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
@endsection

@section('content')

<div class="container">
    <div class="row dashboard-container">

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay"></div>

        <!-- Mobile Sidebar Toggle Button -->
        <button class="mobile-sidebar-toggle d-lg-none">
            <i class="bi bi-list"></i>
        </button>

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
                <a class="nav-link" href="{{url('/home')}}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a class="nav-link active" href="{{url('/user/orders')}}">
                    <i class="bi bi-bag me-2"></i> My Orders
                </a>
                <a class="nav-link" href="{{url('/user/cart')}}">
                    <i class="bi bi-cart me-2"></i> Cart Items
                </a>
                <a class="nav-link" href="{{url('/user/profile')}}">
                    <i class="bi bi-person me-2"></i> My Profile
                </a>
                <a class="nav-link" href="{{url('/change/password')}}">
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


        <div class="col-md-9 col-lg-10 py-4">
            <div class="tab-content">

                <!-- Order Details Content -->
                <div class="tab-pane fade show active" id="orders">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2>Order Details</h2>
                            <p class="text-muted">Order #{{$orderInfo->order_no}}</p>
                        </div>
                        <div class="no-print">
                            <button class="btn btn-outline-secondary me-2" onclick="window.history.back()">
                                <i class="bi bi-arrow-left me-2"></i>Back to Orders
                            </button>
                            <button class="btn btn-primary" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>Print Invoice
                            </button>
                        </div>
                    </div>

                    <!-- Order Status and Summary -->
                    <div class="order-section">
                        <div class="order-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4>Order #{{$orderInfo->order_no}}</h4>
                                <p class="text-muted mb-0">Placed on {{date("jS F Y h:i A", strtotime($orderInfo->order_date))}}</p>
                            </div>
                            <div>
                                @if($orderInfo->payment_status == 0)
                                    <span class="badge bg-danger status-badge">Unpaid</span>
                                @else
                                    <span class="badge bg-success status-badge">Success</span>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <!-- Order Information -->
                            <div class="col-md-8">
                                <div class="order-info mb-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-uppercase fw-bold mb-2">Payment Method</h6>
                                            <p class="mb-0">
                                                <i class="bi bi-credit-card me-2"></i>
                                                @if($orderInfo->payment_method == 2)
                                                bKash
                                                @else
                                                SSLCommerz
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-uppercase fw-bold mb-2">Transaction ID</h6>
                                            <p class="mb-0"><i class="bi bi-truck me-2"></i>{{$orderInfo->trx_id}}</p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Order Timeline -->
                            <div class="col-md-4">
                                <h6 class="text-uppercase fw-bold mb-3">Order Timeline</h6>
                                <div class="timeline">
                                    <div class="timeline-item completed">
                                        <div class="timeline-date">{{date("jS F Y h:i:s A", strtotime($orderInfo->order_date))}}</div>
                                        <div class="fw-semibold">Order Placed</div>
                                    </div>
                                    <div class="timeline-item completed">
                                        @if($orderPayment)
                                        <div class="timeline-date">{{date("jS F Y h:i:s A", strtotime($orderPayment->created_at))}}</div>
                                        <div class="fw-semibold">Payment Confirmed</div>
                                        @else
                                        <div class="timeline-date">N/A</div>
                                        <div class="fw-semibold">Payment Failed</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="order-section">
                        <h5 class="mb-4"><i class="bi bi-box-seam me-2"></i>Order Items</h5>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 20px" class="text-center">SL</th>
                                        <th class="text-center">Book Cover</th>
                                        <th class="text-center">Book Name</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($orderDetails as $index => $orderDetail)
                                    <tr>
                                        <td class="text-center">{{$index + 1}}</td>
                                        <td class="text-center">
                                            <img class="lazy" style="height: 60px" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url(env('ADMIN_URL') . '/' . $orderDetail->image) }}" alt="" class="product-img">
                                        </td>
                                        <td class="text-center">
                                            <h6 class="mb-1">{{$orderDetail->name}}</h6>
                                            <p class="text-muted small mb-0">{{$orderDetail->author_name}}</p>
                                        </td>
                                        <td class="text-center align-middle">{{$orderDetail->qty}}</td>
                                        <td class="text-center align-middle">BDT {{$orderDetail->total_price}}</td>
                                        <td class="text-end align-middle">
                                            @php
                                                $bookURL = env('APP_URL')."/book/".$orderDetail->book_slug;
                                                $packageName = "app.gstl.golpobazar";
                                                $playStoreURL = "https://play.google.com/store/apps/details?id=" . $packageName;
                                                $encodedFallbackURL = urlencode($playStoreURL);
                                            @endphp

                                            <a class="btn btn-sm btn-success rounded"
                                            href="intent://{{ $bookURL }}#Intent;scheme=https;package={{ $packageName }};S.browser_fallback_url={{ $encodedFallbackURL }};end;"
                                            onclick="return handleAppLink(event, '{{ $bookURL }}', '{{ $playStoreURL }}');">
                                            Open in App
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>

                        <!-- Order Summary -->
                        <div class="row">
                            <div class="col-md-6 ms-auto">
                                <div class="order-total">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>BDT {{$orderInfo->sub_total}}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Discount:</span>
                                        <span class="text-danger">- BDT {{$orderInfo->discount}}</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-bold mt-3 pt-3 border-top">
                                        <span>Total:</span>
                                        <span class="text-primary fs-5">BDT {{$orderInfo->total}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection


@section('footer_js')
    <script>

        function handleAppLink(event, bookURL, fallbackURL) {
            if (!navigator.userAgent.match(/Android/i)) {
                // If not on Android, open the web link instead
                event.preventDefault();
                window.location.href = bookURL;
            }
        }

        function changeURL(path) {
            window.history.pushState({}, '', path);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const profileImageInput = document.getElementById('profileImage');
            const profileImage = document.querySelector('.profile-image');

            profileImageInput.addEventListener('change', function(event) {
                if (event.target.files && event.target.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        profileImage.src = e.target.result;
                    }

                    reader.readAsDataURL(event.target.files[0]);
                }
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            const mobileSidebarToggle = document.querySelector('.mobile-sidebar-toggle');
            const sidebarCollapse = document.getElementById('sidebarCollapse');

            // Toggle sidebar on mobile toggle button click
            mobileSidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            });

            // Toggle sidebar on header menu button click
            sidebarCollapse.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            });

            // Close sidebar when clicking on overlay
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });

            // Close sidebar when clicking a nav link on mobile
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.remove('active');
                        sidebarOverlay.classList.remove('active');
                    }
                });
            });

            // Adjust on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
    </script>
@endsection
