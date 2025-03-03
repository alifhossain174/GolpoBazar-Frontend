@extends('master')

@section('content')
    <section>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    {!! $policy->policy !!}
                </div>
            </div>
        </div>
    </section>
@endsection
