@extends('master')

@section('header_css')
    <style>
        .pagination nav{
            box-shadow: none !important;
            background: transparent !important;
        }
        .publishers .publisher_box .publisher_content h5.publisher_name{
            font-size: 16px;
        }
    </style>
@endsection

@section('content')
<section>
    <div class="container">
        <div class="publishers">
            <div class="row publishers_heading">
                <div class="col-lg-8">
                    <h4 class="section_title">Book Publishers</h4>
                </div>
                <div class="col-lg-4 text-end">
                    <input type="search" class="form-control" placeholder="Search for Publishers here">
                </div>
            </div>

            <div class="row">

                @foreach ($publishers as $publisher)
                <div class="col-lg-3">
                    <a href="{{url('publisher/books')}}/{{$publisher->slug}}" class="d-block">
                        <div class="publisher_box">

                            @if($publisher->logo)
                                <img class="publisher_logo lazy" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url(env('ADMIN_URL') . '/' . $publisher->logo) }}" alt="">
                            @else
                                <img class="publisher_logo lazy" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url('assets') }}/images/authors/author.png" alt="">
                            @endif

                            <div class="publisher_content">
                                <h5 class="publisher_name">{{$publisher->name}}</h5>
                                <p><i class="fas fa-book-open"></i> {{DB::table('products')->where('brand_id', $publisher->id)->where('status', 1)->count()}} Books</p>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach

            </div>
        </div>
    </div>
</section>

@if($publishers->total() > 16)
<div class="pagination">
    {{ $publishers->links() }}
</div>
@endif
@endsection
