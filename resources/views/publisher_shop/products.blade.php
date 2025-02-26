<div class="row custom-columns-shop g-4">
    @foreach ($books as $book)
    <div class="col">
        @include('single_book')
    </div>
    @endforeach
</div>

@if($books->total() > 12)
<div class="pagination mt-4">
    {{ $books->links() }}
</div>
@endif
