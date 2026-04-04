@extends('layouts.app')

@section('header')
    <div class="text-[13px] text-gray-500 font-medium">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">{{ __('dashboard.header.breadcrumb_home') }}</a> /
        <span class="text-[#0ea5e9]">{{ __('dashboard.profile.activity_log') }}</span>
    </div>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-[22px] font-bold text-[#1E293B] mb-2">{{ __('dashboard.activity.title') }}</h1>
        <p class="text-gray-500 text-[13px] mb-6">{{ __('dashboard.activity.description') }}</p>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-6">
            <p class="text-[13px] text-gray-600 mb-6 max-w-2xl leading-relaxed">
                {{ __('dashboard.activity.info') }}
            </p>

            <!-- Sessions List -->
            @if (count($sessions) > 0)
                <div class="space-y-6 mb-8">
                    @foreach ($sessions as $session)
                        <div class="flex items-center">
                            <div class="text-gray-400">
                                @if ($session->agent->isDesktop)
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                @else
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                @endif
                            </div>

                            <div class="ml-4">
                                <div class="text-[13px] font-medium text-gray-800">
                                    {{ $session->agent->platform ? $session->agent->platform : 'Unknown' }} -
                                    {{ $session->agent->browser ? $session->agent->browser : 'Unknown' }}
                                </div>
                                <div class="text-[12px] text-gray-500">
                                    {{ $session->ip_address }},
                                    @if ($session->is_current_device)
                                        <span
                                            class="text-green-500 font-medium">{{ __('dashboard.activity.this_device') }}</span>
                                    @else
                                        {{ __('dashboard.activity.last_active') }} {{ $session->last_active }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('profile.activity.logout-others') }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="bg-[#111827] hover:bg-black text-white text-[13px] font-medium py-2 px-6 rounded-md shadow-sm transition-colors">
                    {{ __('dashboard.activity.logout_other') }}
                </button>
            </form>

            @if (session('status') === 'browser-sessions-terminated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600 font-medium mt-4">{{ __('dashboard.activity.logout_success') }}</p>
            @endif
        </div>
    </div>
@endsection
