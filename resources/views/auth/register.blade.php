@extends('layouts.guest')

@section('title', __('auth.register') . ' - ' . config('app.name', 'web-dabb'))

@section('body-class')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@section('content')
    <div class="login-breadcrumb">
        <div class="container">
            <span class="text-cyan">{{ __('auth.register') }}</span>
        </div>
    </div>

    <div class="login-hero" style="height: 200px;">
        <div class="container">
            <h1>{{ __('auth.register_title') }}</h1>
        </div>
    </div>

    <main class="flex-grow login-main-wrapper" style="padding: 40px 5%;">
        <div class="register-card">

            <!-- Left Side: Form Area -->
            <div class="login-form-side" style="padding: 50px 60px;">
                <h2 style="font-size: 26px; margin-bottom: 10px; color: #495057;">{{ __('auth.register_heading') }}</h2>
                <p class="subtitle" style="margin-bottom: 40px;">{{ __('auth.register_subtitle') }}</p>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Role Selector -->
                <div class="role-selector-wrapper" id="initial-selector">
                    <select class="role-selector" id="role-select" onchange="showForm()">
                        <option value="" disabled selected>{{ __('auth.select_account_type') }}</option>
                        <option value="umum">{{ __('auth.role_umum') }}</option>
                        <option value="pelajar">{{ __('auth.role_pelajar') }}</option>
                        <option value="instansi">{{ __('auth.role_instansi') }}</option>
                    </select>

                    <div class="login-divider" style="margin: 30px 0 20px 0; display: flex; align-items: center; text-align: center; color: #a0aec0;">
                        <span style="flex: 1; border-bottom: 1px solid #e2e8f0;"></span>
                        <span style="padding: 0 10px; font-size: 14px;">{{ __('auth.or') }}</span>
                        <span style="flex: 1; border-bottom: 1px solid #e2e8f0;"></span>
                    </div>

                    <a href="{{ route('auth.google.register') }}" class="btn-google" style="display: flex; justify-content: center; align-items: center; width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; color: #4a5568; font-weight: 600; text-decoration: none; transition: all 0.2s ease;">
                        <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Google" style="margin-right: 10px; width: 24px;">
                        Daftar dengan Google
                    </a>
                </div>

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
                    id="registration-form" style="display: none;">
                    @csrf

                    <!-- Hidden input to store role for backend -->
                    <input type="hidden" name="role" id="form-role-input">

                    <div class="form-grid">
                        <!-- Nama -->
                        <label for="name" class="required" id="label-name">{{ __('auth.full_name') }}</label>
                        <input type="text" name="name" id="name" class="login-input"
                            placeholder="{{ __('auth.full_name') }}" value="{{ old('name') }}" required>

                        <!-- Username -->
                        <label for="username" class="required">{{ __('auth.username') }}</label>
                        <input type="text" name="username" id="username" class="login-input" placeholder="{{ strtolower(__('auth.username')) }}"
                            value="{{ old('username') }}" required>

                        <!-- Email -->
                        <label for="email" class="required">{{ __('Email') }}</label>
                        <input type="email" name="email" id="email" class="login-input"
                            placeholder="nama@gmail.com" value="{{ old('email') }}" required>

                        <div class="jk-group" style="display: contents;">
                            <label class="required">{{ __('auth.gender') }}</label>
                            <div class="radio-group" id="jk-container">
                                @php
                                    $jkList = \App\Models\User::getEnumValues('user_umums', 'jenis_kelamin');
                                @endphp
                                @foreach($jkList as $jk)
                                    <label class="radio-item">
                                        <input type="radio" name="jenis_kelamin" value="{{ $jk }}"
                                            {{ old('jenis_kelamin') == $jk ? 'checked' : '' }}> {{ $jk }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Tempat & Tanggal Lahir -->
                        <label for="tempat_lahir" class="required" id="label-tempat-lahir">{{ __('auth.birth_place_date') }}</label>
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 15px;">
                            <input type="text" name="tempat_lahir" id="tempat_lahir" class="login-input"
                                placeholder="{{ __('auth.birth_place') }}" value="{{ old('tempat_lahir') }}" required>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="login-input"
                                value="{{ old('tanggal_lahir') }}" required>
                        </div>

                        <!-- Kartu Identitas Upload -->
                        <label for="kartu_identitas" class="required" id="label-kartu-identitas">{{ __('auth.identity_card') }}</label>
                        <div class="file-upload-wrapper">
                            <button type="button" class="file-upload-btn"
                                onclick="document.getElementById('kartu_identitas').click()">{{ __('auth.choose_file') }}</button>
                            <span class="file-upload-text" id="file-name">{{ __('auth.no_file') }}</span>
                            <input type="file" name="kartu_identitas" id="kartu_identitas"
                                accept=".jpg,.jpeg,.png,.pdf" onchange="updateFileName(this)" required>
                        </div>

                        <!-- Nomor Kartu Identitas -->
                        <label for="nomor_kartu_identitas" class="required">{{ __('auth.identity_number') }}</label>
                        <input type="text" name="nomor_kartu_identitas" id="nomor_kartu_identitas"
                            class="login-input" placeholder="{{ __('auth.identity_number') }}"
                            value="{{ old('nomor_kartu_identitas') }}" required>

                        <!-- Alamat -->
                        <label for="alamat" class="required">{{ __('auth.address') }}</label>
                        <input type="text" name="alamat" id="alamat" class="login-input"
                            placeholder="{{ __('auth.address') }}" value="{{ old('alamat') }}" required>

                        <!-- Whatsapp -->
                        <label for="nomor_whatsapp" class="required">{{ __('auth.whatsapp') }}</label>
                        <input type="text" name="nomor_whatsapp" id="nomor_whatsapp" class="login-input"
                            placeholder="0831xxxxxxxx" value="{{ old('nomor_whatsapp') }}" required>

                        <!-- Divider inside grid -->
                        <div style="grid-column: 1 / -1;">
                            <hr>
                        </div>

                        <!-- Password -->
                        <label for="password" class="required">{{ __('Password') }}</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="login-input"
                                placeholder="{{ __('Password') }}" required>
                        </div>

                        <!-- Confirm Password -->
                        <label for="password_confirmation" class="required">{{ __('auth.confirm_password') }}</label>
                        <div class="password-wrapper">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="login-input" placeholder="{{ __('auth.confirm_password') }}" required>
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <hr>
                        </div>

                        <!-- Jenis Keperluan -->
                        <label for="jenis_keperluan" class="required">{{ __('auth.purpose_type') }}</label>
                        <select name="jenis_keperluan" id="jenis_keperluan" class="login-input" required>
                            <option value="">{{ __('auth.select_purpose') }}</option>
                            <option value="Hanya Daftar Akun"
                                {{ old('jenis_keperluan') == 'Hanya Daftar Akun' ? 'selected' : '' }}>{{ __('auth.purpose_register_only') }}
                            </option>
                            <option value="Penelitian" {{ old('jenis_keperluan') == 'Penelitian' ? 'selected' : '' }}>
                                {{ __('auth.purpose_research') }}</option>
                            <option value="Kunjungan" {{ old('jenis_keperluan') == 'Kunjungan' ? 'selected' : '' }}>
                                {{ __('auth.purpose_visit') }}</option>
                        </select>

                        <!-- Judul Keperluan -->
                        <label for="judul_keperluan" class="required">{{ __('auth.purpose_title') }}</label>
                        <input type="text" name="judul_keperluan" id="judul_keperluan" class="login-input"
                            value="{{ old('judul_keperluan') }}" required>

                    </div>

                    <div style="text-align: right; margin-top: 30px;">
                        <button type="submit" class="btn-submit-form">{{ __('auth.register_button') }}</button>
                    </div>

                </form>

            </div>

            <!-- Right Side: Image Banner -->
            <div class="login-banner-side" style="flex: 0.8;">
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

@push('scripts')
    <script>
        var authTranslations = {
            fullName: @json(__('auth.full_name')),
            institutionName: @json(__('auth.institution_name')),
            identityCardKtp: @json(__('auth.identity_card_ktp')),
            identityCardKtm: @json(__('auth.identity_card_ktm')),
            identityCardInstansi: @json(__('auth.identity_card_instansi')),
            noFile: @json(__('auth.no_file')),
        };
    </script>
    <input type="hidden" id="old-role-value" value="{{ old('role') }}">
    <script src="{{ asset('js/register.js') }}" defer></script>
@endpush
