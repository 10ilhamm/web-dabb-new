@extends('layouts.guest')

@section('title', __('auth.login') . ' - ' . config('app.name', 'Laravel'))

@section('body-class')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
    <!-- Breadcrumb -->
    <div class="login-breadcrumb">
        <div class="container">
            <span class="text-cyan">{{ __('auth.login') }}</span>
        </div>
    </div>

    <!-- Hero Header -->
    <div class="login-hero">
        <div class="container">
            <h1>{{ strtoupper(__('auth.login')) }}</h1>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow login-main-wrapper">
        <div class="login-card">

            <!-- Left Side: Form -->
            <div class="login-form-side">
                <h2>{{ __('auth.welcome') }}</h2>

                <h3>{{ __('auth.login') }}</h3>
                <p class="subtitle">{{ __('auth.login_subtitle') }}</p>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="login-form-group">
                        <label for="email">{{ __('auth.email') }}</label>
                        <input id="email" class="login-input @error('email') login-input-error @enderror" type="email" name="email"
                            value="{{ old('email') }}" required autofocus autocomplete="username" />
                        @error('email')
                            <p class="login-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="login-form-group">
                        <label for="password">{{ __('auth.field_password') }}</label>
                        <input id="password" class="login-input @error('password') login-input-error @enderror" type="password" name="password" required
                            autocomplete="current-password" />
                        @error('password')
                            <p class="login-error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Forgot Password Link -->
                    @if (Route::has('password.request'))
                        <a class="login-forgot" href="{{ route('password.request') }}">
                            {{ __('auth.forgot_password') }}
                        </a>
                    @endif

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">
                        {{ __('auth.submit') }}
                    </button>

                    <div class="login-divider">{{ __('auth.or') }}</div>

                    <!-- Google Login Button (Placeholder href) -->
                    <a href="{{ route('auth.google.login') }}" class="btn-google">
                        <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Google">
                        {{ __('auth.login_google') }}
                    </a>

                    <!-- Register Link -->
                    <div class="login-register-text">
                        {{ __('auth.no_account') }} <a href="{{ route('register') }}">{{ __('auth.register_link') }}</a>
                    </div>
                </form>
            </div>

            <!-- Right Side: Image Banner -->
            <div class="login-banner-side">
                <div class="banner-overlay-logo">
                    <img src="{{ asset('image/logo_anri.png') }}" alt="ANRI Logo">
                    <div class="banner-overlay-text">
                        <div class="title">{!! __('auth.banner_title') !!}</div>
                    </div>
                </div>
            </div>

        </div>
    </main>
@endsection
