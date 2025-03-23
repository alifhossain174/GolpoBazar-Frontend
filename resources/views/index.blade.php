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

    @include('homepage_sections.random_pick')
    @include('homepage_sections.new_books')

    @php
        $halfCountCategories = floor(count($categories)/2);
    @endphp

    @foreach ($categories as $categoryLoopIndex => $category)

        @if($categoryLoopIndex == $halfCountCategories)
            @include('homepage_sections.featured_authors')
        @endif

        @include('homepage_sections.categorywise_books')

    @endforeach

@endsection
