<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>My Orders</h2>
</div>

@php
    $userOrders = DB::table('orders')
                    ->where('user_id', Auth::user()->id)
                    ->orderBy('id', 'desc')
                    ->paginate(10);
@endphp

<!-- Recent orders table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
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
                    @if(count($userOrders) > 0)
                    @foreach ($userOrders as $userOrderIndex => $userOrder)
                    <tr>
                        <td class="text-center">{{++$userOrderIndex}}</td>
                        <td class="text-center">{{$userOrder->order_no}}</td>
                        <td class="text-center">{{date("jS F Y h:i:s A", strtotime($userOrder->order_date))}}</td>
                        <td class="text-center">
                            {{DB::table('order_details')->where('order_id', $userOrder->id)->count()}} items
                        </td>
                        <td class="text-center">BDT {{$userOrder->total}}</td>
                        <td class="text-center">
                            @if($userOrder->payment_status == 0)
                                <span class="badge bg-danger status-badge">Unpaid</span>
                            @else
                                <span class="badge bg-success status-badge">Success</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{url('order/details')}}/{{$userOrder->order_no}}" class="btn btn-sm btn-outline-secondary">View</a>
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

            {{ $userOrders->links() }}

        </div>
    </div>
</div>
