<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Dashboard</h2>
</div>

<!-- Stats cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">TOTAL ORDERS</h6>
                <h2 class="card-title mb-2">{{DB::table('orders')->where('user_id', Auth::user()->id)->count()}}</h2>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">TOTAL SPENT</h6>
                <h2 class="card-title mb-2">BDT {{number_format(DB::table('orders')->where('user_id', Auth::user()->id)->sum('total'))}}</h2>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">TOTAL BOOKS</h6>
                <h2 class="card-title mb-2">
                    @php
                        $totalOrdersBooks = DB::table('order_details')
                                                ->leftJoin('orders', 'order_details.order_id', '=', 'orders.id')
                                                ->where('orders.user_id', Auth::user()->id)
                                                ->groupBy('order_details.product_id')
                                                ->count();
                    @endphp
                    {{$totalOrdersBooks}}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">CART ITEMS</h6>
                <h2 class="card-title mb-2">{{ session('cart') ? count(session('cart')) : 0 }}</h2>
            </div>
        </div>
    </div>
</div>

@php
    $recentOrders = DB::table('orders')
                    ->where('user_id', Auth::user()->id)
                    ->orderBy('id', 'desc')
                    ->limit(5)
                    ->get();
@endphp

<!-- Recent orders table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-3">Recent Orders</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">SL</th>
                        <th class="text-center">Order No</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Items</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($recentOrders) > 0)
                    @foreach ($recentOrders as $index => $recentOrder)
                    <tr>
                        <td class="text-center">{{++$index}}</td>
                        <td class="text-center">{{$recentOrder->order_no}}</td>
                        <td class="text-center">{{date("jS F Y h:i:s A", strtotime($recentOrder->order_date))}}</td>
                        <td class="text-center">
                            {{DB::table('order_details')->where('order_id', $recentOrder->id)->count()}} items
                        </td>
                        <td class="text-center">BDT {{$recentOrder->total}}</td>
                        <td class="text-center">
                            @if($recentOrder->payment_status == 0)
                                <span class="badge bg-danger status-badge">Unpaid</span>
                            @else
                                <span class="badge bg-success status-badge">Success</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{url('order/details')}}/{{$recentOrder->order_no}}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="7" class="text-center">No orders found</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
