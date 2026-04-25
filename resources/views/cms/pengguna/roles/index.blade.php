@extends('layouts.app')

@section('breadcrumb_items')
    <span class="text-gray-400">CMS</span>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('cms.pengguna.index') }}"
        class="text-gray-500 hover:text-gray-700">{{ __('cms.pengguna.breadcrumb') }}</a>
@endsection
@section('breadcrumb_active', __('cms.roles.title'))

@section('content')
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ __('cms.roles.title') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('cms.roles.subtitle') }}</p>
            </div>
            <a href="{{ route('cms.pengguna.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                {{ __('cms.pengguna.back') }}
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-start justify-between gap-2">
                <div>
                    <p class="text-sm text-gray-500">{{ __('cms.roles.stats_total') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-lg bg-blue-50 text-[#174E93] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-start justify-between gap-2">
                <div>
                    <p class="text-sm text-gray-500">{{ __('cms.roles.stats_system') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['system'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-start justify-between gap-2">
                <div>
                    <p class="text-sm text-gray-500">{{ __('cms.roles.stats_custom') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['custom'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Add Role Button --}}
        <div class="flex justify-end">
            <a href="{{ route('cms.pengguna.roles.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-[#174E93] hover:bg-blue-500 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                {{ __('cms.roles.add_button') }}
            </a>
        </div>

        {{-- Roles Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {{ __('cms.roles.col_name') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {{ __('cms.roles.col_label') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {{ __('cms.roles.col_table') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {{ __('cms.roles.col_type') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {{ __('cms.roles.col_users') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">
                                {{ __('cms.pengguna.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr class="hover:bg-gray-50/50 transition-colors border-b border-gray-50 last:border-b-0">
                                <td class="px-6 py-4">
                                    <code
                                        class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $role->name }}</code>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800">{{ $role->label }}</td>
                                <td class="px-6 py-4 text-gray-600 text-sm">
                                    @if ($role->table_name)
                                        <code
                                            class="font-mono bg-gray-50 px-1.5 py-0.5 rounded text-xs">{{ $role->table_name }}</code>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($role->is_system)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-100">
                                            {{ __('cms.roles.type_system') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-100">
                                            {{ __('cms.roles.type_custom') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600 text-sm">{{ $role->users()->count() }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('cms.pengguna.roles.edit', $role) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-colors"
                                            title="{{ __('cms.pengguna.edit_button') }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        @if (!$role->is_system)
                                            <form action="{{ route('cms.pengguna.roles.destroy', $role) }}" method="POST"
                                                onsubmit="return confirm('{{ __('cms.roles.delete_confirm', ['name' => $role->label]) }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-md transition-colors"
                                                    title="{{ __('cms.pengguna.delete_button') }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                        <p class="text-gray-400 text-sm">{{ __('cms.roles.empty') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
