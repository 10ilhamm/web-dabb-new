@extends('layouts.guest')

@section('title', $title . ' - ' . config('app.name', 'web-dabb'))

@section('body-class')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
    <main class="flex-grow login-main-wrapper" style="padding: 40px 5%;">
        <div style="max-width: 500px; margin: 0 auto;">

            <!-- Success Card -->
            @if ($type === 'success')
                <div
                    style="background: #ffffff; border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                    <div
                        style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px auto;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h2 style="color: #28a745; font-size: 24px; margin: 0 0 15px 0;">{{ $title }}</h2>
                    <p style="color: #555555; font-size: 15px; line-height: 1.7; margin: 0 0 30px 0;">{{ $message }}
                    </p>
                    <a href="{{ route('dashboard') }}"
                        style="display: inline-block; background: linear-gradient(135deg, #0579CB 0%, #034a8a 100%); color: #ffffff; padding: 14px 35px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px;">
                        <i class="fas fa-home"></i> Menuju Dashboard
                    </a>
                </div>

                <!-- Info Card -->
            @elseif($type === 'info')
                <div
                    style="background: #ffffff; border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                    <div
                        style="width: 80px; height: 80px; background: linear-gradient(135deg, #0579CB 0%, #034a8a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px auto;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                    </div>
                    <h2 style="color: #0579CB; font-size: 24px; margin: 0 0 15px 0;">{{ $title }}</h2>
                    <p style="color: #555555; font-size: 15px; line-height: 1.7; margin: 0 0 30px 0;">{{ $message }}
                    </p>
                    <a href="{{ route('dashboard') }}"
                        style="display: inline-block; background: linear-gradient(135deg, #0579CB 0%, #034a8a 100%); color: #ffffff; padding: 14px 35px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px;">
                        <i class="fas fa-home"></i> Menuju Dashboard
                    </a>
                </div>

                <!-- Warning Card -->
            @elseif($type === 'warning')
                <div
                    style="background: #ffffff; border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                    <div
                        style="width: 80px; height: 80px; background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px auto;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h2 style="color: #fd7e14; font-size: 24px; margin: 0 0 15px 0;">{{ $title }}</h2>
                    <p style="color: #555555; font-size: 15px; line-height: 1.7; margin: 0 0 30px 0;">{{ $message }}
                    </p>
                    <a href="{{ route('verification.notice') }}"
                        style="display: inline-block; background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%); color: #ffffff; padding: 14px 35px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px;">
                        <i class="fas fa-paper-plane"></i> Minta Link Baru
                    </a>
                </div>

                <!-- Error Card -->
            @else
                <div
                    style="background: #ffffff; border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                    <div
                        style="width: 80px; height: 80px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px auto;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <h2 style="color: #dc3545; font-size: 24px; margin: 0 0 15px 0;">{{ $title }}</h2>
                    <p style="color: #555555; font-size: 15px; line-height: 1.7; margin: 0 0 30px 0;">{{ $message }}
                    </p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="{{ route('login') }}"
                            style="display: inline-block; background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: #ffffff; padding: 14px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px;">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="{{ route('register') }}"
                            style="display: inline-block; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #ffffff; padding: 14px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px;">
                            <i class="fas fa-user-plus"></i> Daftar Ulang
                        </a>
                    </div>
                </div>
            @endif

            <!-- ANRI Branding -->
            <div style="display: flex; flex-direction: column; align-items: center; text-align: center; margin-top: 30px;">
                <img src="{{ asset('image/logo_anri.png') }}" alt="Logo ANRI" style="height: 50px; opacity: 0.7;">
                <p style="color: #888888; font-size: 12px; margin: 10px 0 0 0;">
                    Depot Arsip Berkelanjutan Bandung<br>
                    Lembaga Kearsipan Nasional Republik Indonesia
                </p>
            </div>

        </div>
    </main>
@endsection
