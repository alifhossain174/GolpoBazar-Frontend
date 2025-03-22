@extends('master')

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
        }

        @media (max-width: 767.98px) {
            .sidebar {
                height: auto;
                margin-bottom: 20px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row dashboard-container">

            @include('dashboard.menu')

            <!-- Main content area with tab content -->
            <div class="col-md-9 col-lg-10 py-4">
                <div class="tab-content">

                    <!-- Dashboard tab -->
                    <div class="tab-pane @if(Request::path() == 'home') show active @else fade @endif" id="dashboard">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Dashboard</h2>
                        </div>

                        <!-- Stats cards -->
                        <div class="row g-4 mb-4">
                            <div class="col-sm-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">TOTAL ORDERS</h6>
                                        <h2 class="card-title mb-2">248</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">TOTAL SPENT</h6>
                                        <h2 class="card-title mb-2">$8,492</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">WISHLISTED ITEMS</h6>
                                        <h2 class="card-title mb-2">16</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">CART ITEMS</h6>
                                        <h2 class="card-title mb-2">3</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent orders table -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Recent Orders</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Date</th>
                                                <th>Items</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>#ORD-7412</td>
                                                <td>Mar 18, 2025</td>
                                                <td>3 items</td>
                                                <td>$158.99</td>
                                                <td><span class="badge bg-success status-badge">Delivered</span></td>
                                                <td><button class="btn btn-sm btn-outline-secondary">View</button></td>
                                            </tr>
                                            <tr>
                                                <td>#ORD-7411</td>
                                                <td>Mar 15, 2025</td>
                                                <td>1 item</td>
                                                <td>$49.99</td>
                                                <td><span class="badge bg-warning text-dark status-badge">Shipped</span>
                                                </td>
                                                <td><button class="btn btn-sm btn-outline-secondary">View</button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other tabs with placeholder content -->
                    <div class="tab-pane fade" id="orders">
                        <h2>Orders</h2>
                        <p>Your order history will appear here.</p>
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
    </script>
@endsection
