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
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .b-example-divider {
            width: 100%;
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            vertical-align: -.125em;
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .btn-bd-primary {
            --bd-violet-bg: #712cf9;
            --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

            --bs-btn-font-weight: 600;
            --bs-btn-color: var(--bs-white);
            --bs-btn-bg: var(--bd-violet-bg);
            --bs-btn-border-color: var(--bd-violet-bg);
            --bs-btn-hover-color: var(--bs-white);
            --bs-btn-hover-bg: #6528e0;
            --bs-btn-hover-border-color: #6528e0;
            --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
            --bs-btn-active-color: var(--bs-btn-hover-color);
            --bs-btn-active-bg: #5a23c8;
            --bs-btn-active-border-color: #5a23c8;
        }

        .bd-mode-toggle {
            z-index: 1500;
        }

        .bd-mode-toggle .dropdown-menu .active .bi {
            display: block !important;
        }

        .form-signin {
            max-width: 330px;
            padding: 1rem;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
@endsection

@section('content')

    @php
        $generalInfo = DB::table('general_infos')->where('id', 1)->first();
    @endphp

    <main class="form-signin w-100 m-auto" style="max-width: 350px; padding: 1.5rem; background: white; border-radius: 8px;">
        <form method="POST" action="{{ url('send/forget/password/code') }}">
            @csrf
            <div class="row">
                <div class="col-lg-12 text-center">
                    <img src="{{ url(env('ADMIN_URL') . '/' . $generalInfo->logo) }}" alt="" width="72" height="72" class="mb-4">
                </div>
            </div>
            <h1 class="h3 mb-3 fw-normal" style="text-align: center; font-size: 22px; font-weight: 600 !important;">Forgotten Password?</h1>

            <div class="form-floating">
                <input name="username" type="text" autocomplete="off" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="01*********" required="" />
                <label for="username">Mobile No</label>
            </div>

            @error('username')
                <span class="invalid-feedback" role="alert" style="display: block;">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

            <button class="btn btn-primary w-100 py-2 mt-3" style="background: #20B1B6" type="submit">Send Code</button>
        </form>

        <p class="mt-4">
            Remember credentials? <a href="{{url('login')}}">Sign in</a>
        </p>
    </main>
@endsection
