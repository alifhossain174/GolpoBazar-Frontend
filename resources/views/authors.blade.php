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
                        <h4 class="section_title">Book Authors</h4>
                    </div>
                    <div class="col-lg-4 text-end">
                        {{-- <input type="search" class="form-control" placeholder="Search for Authors here"> --}}
                    </div>
                </div>

                <div class="row">

                    {{-- loop here --}}
                    @foreach ($authors as $author)
                    <div class="col-lg-3">
                        <a href="{{url('author/books')}}/{{$author->id}}" class="d-block">
                            <div class="publisher_box">

                                @if($author->image)
                                    <img class="publisher_logo lazy" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url(env('ADMIN_URL') . '/' . $author->image) }}" alt="">
                                @else
                                    <img class="publisher_logo lazy" src="{{ url('assets') }}/images/product-load.gif" data-src="{{ url('assets') }}/images/authors/author.png" alt="">
                                @endif

                                <div class="publisher_content">
                                    <h5 class="publisher_name">{{$author->name}}</h5>
                                    <p><i class="fas fa-book-open"></i> {{DB::table('products')->where('author_id', $author->id)->where('status', 1)->count()}} Books</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </section>

    @if($authors->total() > 16)
    <div class="pagination">
        {{ $authors->links() }}
    </div>
    @endif

@endsection
