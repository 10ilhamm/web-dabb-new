@extends('layouts.app')

@section('breadcrumb_items')
    <span class="text-gray-400">CMS</span>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('cms.pengguna.index') }}"
        class="text-gray-500 hover:text-gray-700">{{ __('cms.pengguna.breadcrumb') }}</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('cms.pengguna.roles.index') }}"
        class="text-gray-500 hover:text-gray-700">{{ __('cms.roles.title') }}</a>
@endsection
@section('breadcrumb_active', __('cms.roles.create_title'))

@section('content')
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ __('cms.roles.create_title') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('cms.roles.create_subtitle') }}</p>
            </div>
        </div>

        <form action="{{ route('cms.pengguna.roles.store') }}" method="POST"
            class="bg-white rounded-xl shadow-sm border border-gray-100">
            @csrf

            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">{{ __('cms.roles.create_title') }}</h2>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms.roles.form_name') }} <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="{{ __('cms.roles.form_name_placeholder') }}"
                        class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <p class="text-xs text-amber-600 mt-1">{{ __('cms.roles.form_name_warning') }}</p>
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Label --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms.roles.form_label') }} <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="label" value="{{ old('label') }}" required
                        placeholder="{{ __('cms.roles.form_label_placeholder') }}"
                        class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    @error('label')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Type (is_system) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms.roles.form_type') }} <span
                            class="text-red-500">*</span></label>
                    <div class="flex items-center gap-4 mt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_system" value="1"
                                {{ old('is_system') == '1' ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">{{ __('cms.roles.type_system') }}</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_system" value="0"
                                {{ old('is_system', '0') == '0' ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">{{ __('cms.roles.type_custom') }}</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ __('cms.roles.form_type_help') }}</p>
                    @error('is_system')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Table Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms.roles.form_table_name') }}
                        <span class="text-red-500">*</span></label>
                    <input type="text" name="table_name" value="{{ old('table_name') }}" required
                        placeholder="{{ __('cms.roles.form_table_name_placeholder') }}"
                        class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <p class="text-xs text-gray-400 mt-1">{{ __('cms.roles.form_table_name_help') }}</p>
                    @error('table_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Relation Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms.roles.form_relation_name') }}
                        <span class="text-red-500">*</span></label>
                    <input type="text" name="relation_name" value="{{ old('relation_name') }}" required
                        placeholder="{{ __('cms.roles.form_relation_name_placeholder') }}"
                        class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <p class="text-xs text-gray-400 mt-1">{{ __('cms.roles.form_relation_name_help') }}</p>
                    @error('relation_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label
                        class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms.roles.form_description') }}</label>
                    <textarea name="description" rows="3" placeholder="{{ __('cms.roles.form_description_placeholder') }}"
                        class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-6 py-4 flex items-center justify-end gap-3 bg-gray-50/50 rounded-b-xl">
                <a href="{{ route('cms.pengguna.roles.index') }}"
                    class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    {{ __('cms.pengguna.cancel') }}
                </a>
                <button type="submit"
                    class="px-4 py-2.5 text-sm font-semibold text-white bg-[#174E93] hover:bg-blue-800 rounded-lg transition-colors">
                    {{ __('cms.pengguna.save') }}
                </button>
            </div>
        </form>
    </div>
@endsection
