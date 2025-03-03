@extends('master')

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
