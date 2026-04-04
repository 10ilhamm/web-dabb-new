@extends('layouts.app')

@section('header')
    <div class="text-[13px] text-gray-500 font-medium">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">{{ __('dashboard.header.breadcrumb_home') }}</a> /
        <span class="text-[#0ea5e9]">{{ __('dashboard.sidebar.home') }}</span>
    </div>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-[22px] font-bold text-[#1E293B] mb-2">
            {{ __('dashboard.welcome.greeting_pegawai', ['name' => auth()->user()->name]) }}</h1>
        <p class="text-gray-500 text-sm">{{ __('dashboard.welcome.subtitle_pegawai') }}</p>

        <!-- Content Card -->
        <div class="mt-8 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">{{ __('dashboard.welcome.recent_activity') }}</h2>
            <div class="text-sm text-gray-500">{{ __('dashboard.welcome.no_activity') }}</div>
        </div>
    </div>
@endsection
