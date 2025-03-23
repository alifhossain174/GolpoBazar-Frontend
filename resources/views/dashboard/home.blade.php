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


            @include('dashboard.menu')

            <!-- Main content area with tab content -->
            <div class="col-md-9 col-lg-10 py-4">
                <div class="tab-content">

                    <div class="tab-pane @if(Request::path() == 'home') show active @else fade @endif" id="dashboard">
                        @include('dashboard.homepage')
                    </div>

                    <div class="tab-pane @if(Request::path() == 'user/orders') show active @else fade @endif" id="orders">
                        @include('dashboard.orders')
                    </div>

                    <div class="tab-pane @if(Request::path() == 'user/cart') show active @else fade @endif" id="cart">
                        @include('dashboard.cart')
                    </div>

                    <div class="tab-pane @if(Request::path() == 'user/profile') show active @else fade @endif" id="profile">
                        @include('dashboard.profile')
                    </div>

                    <div class="tab-pane @if(Request::path() == 'change/password') show active @else fade @endif" id="password">
                        @include('dashboard.change_password')
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('footer_js')
    <script>

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
