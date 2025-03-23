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

@section('content')

<div class="container text-center mt-5">
    <div class="card p-4 shadow-lg">
        <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" alt="Success" width="120" class="mx-auto">
        <h3 class="mt-3 text-success">Order Placed Successfully!</h3>
        <p class="text-muted">Your order number is <strong>#{{$orderInfo->order_no}}</strong></p>
        <a href="{{url('order/details')}}/{{$orderInfo->order_no}}" class="btn btn-primary mt-3">View Order Details</a>
    </div>
</div>

@endsection
