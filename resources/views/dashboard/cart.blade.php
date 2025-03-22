<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>View Products in Cart</h2>
    <a class="btn btn-primary" href="{{url('view/cart')}}">
        <i class="fas fa-cart-plus"></i> Proceed to Checkout
    </a>
</div>

<!-- Recent orders table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Cover Image</th>
                        <th class="text-center">Book Name</th>
                        <th class="text-center">Author Name</th>
                        <th class="text-center">Qunatity</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(session('cart') && count(session('cart')))
                        @foreach(session('cart') as $id => $details)
                        <tr>
                            <td class="text-center">
                                <a href="{{url('book')}}/{{$details['slug']}}" class="cartItemImage">
                                    <img class="lazy" style="height: 60px" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url(env('ADMIN_URL') . '/' . $details['image']) }}" alt="">
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="{{url('book')}}/{{$details['slug']}}" style="text-decoration: none; color: #1e1e1e">
                                    {{$details['name']}}
                                </a>
                            </td>
                            <td class="text-center">{{$details['author_name']}}</td>
                            <td class="text-center">{{$details['quantity']}}</td>
                            <td class="text-center">
                                @if($details['discount_price'] && $details['discount_price'] < $details['price'])
                                    <small><del>৳{{number_format($details['price'])}}</del></small>
                                    ৳{{number_format($details['discount_price'])}}
                                @else
                                    ৳{{number_format($details['price'])}}
                                @endif
                            </td>
                            <td class="text-center">
                                <style>
                                    .userdashboardcartBtn.removeFromCart{
                                        color: white;
                                    }
                                    .userdashboardcartBtn.addToCart{
                                        color: #1e1e1e;
                                    }
                                </style>
                                <button data-id="{{$id}}" class="userdashboardcartBtn cart-{{$id}} removeFromCart btn btn-sm add-to-cart-btn"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No Books found in Cart</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
