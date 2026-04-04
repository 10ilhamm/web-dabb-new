@extends('layouts.app')

@section('header')
    <div class="text-[13px] text-gray-500 font-medium">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">{{ __('dashboard.header.breadcrumb_home') }}</a> /
        <span class="text-[#0ea5e9]">{{ __('dashboard.instansi.title') }}</span>
    </div>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-[22px] font-bold text-[#1E293B] mb-2">
            {{ __('dashboard.welcome.greeting_instansi', ['name' => auth()->user()->name]) }}</h1>
        <p class="text-gray-500 text-sm">{{ __('dashboard.welcome.subtitle_instansi') }}</p>

        <!-- Content Card -->
        <div class="mt-8 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">{{ __('dashboard.instansi.card_title') }}</h2>
            <p class="text-sm text-gray-600 mb-4">{{ __('dashboard.instansi.card_desc') }}</p>
            <div class="flex space-x-3">
                <button
                    class="bg-[#174E93] text-white px-4 py-2 rounded shadow hover:bg-blue-800 transition-colors text-sm font-medium">{{ __('dashboard.instansi.card_button1') }}</button>
                <button
                    class="bg-gray-100 text-gray-700 px-4 py-2 rounded border border-gray-300 hover:bg-gray-200 transition-colors text-sm font-medium">{{ __('dashboard.instansi.card_button2') }}</button>
            </div>
        </div>
    </div>
@endsection
