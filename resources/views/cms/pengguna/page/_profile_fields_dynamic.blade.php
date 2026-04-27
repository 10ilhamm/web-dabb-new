{{-- Dynamic profile fields based on role_columns --}}
{{-- Usage: @include('cms.pengguna.page._profile_fields_dynamic', ['role' => 'admin', 'profile' => $profile, 'enumOptions' => $enumOptions]) --}}

@php
    // Group profile columns into display categories
    $skipFields = ['user_id', 'id', 'created_at', 'updated_at'];
    $textAreaFields = collect();
    $fileFields = collect();
    $selectFields = collect();
    $inputFields = collect();

    foreach ($profileColumns as $col) {
        if (in_array($col->column_name, $skipFields)) continue;

        if (in_array($col->column_type, ['text', 'longtext', 'mediumtext'])) {
            $textAreaFields->push($col);
        } elseif ($col->column_type === 'blob') {
            $fileFields->push($col);
        } elseif (in_array($col->column_type, ['enum', 'set'])) {
            $selectFields->push($col);
        } else {
            $inputFields->push($col);
        }
    }
@endphp

{{-- Dynamic input fields --}}
@foreach($inputFields as $col)
    @php
        $value = old($col->column_name, $profile?->{$col->column_name} ?? '');
    @endphp
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $col->column_label ?? str()->headline($col->column_name) }}
            @if(!$col->is_nullable)<span class="text-red-500">*</span>@endif
        </label>

        @if(in_array($col->column_type, ['date', 'datetime', 'timestamp']))
            <input type="{{ $col->column_type === 'date' ? 'date' : 'datetime-local' }}"
                name="{{ $col->column_name }}"
                value="{{ $value }}"
                class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @elseif(in_array($col->column_type, ['int', 'bigint', 'smallint', 'tinyint']))
            <input type="number"
                name="{{ $col->column_name }}"
                value="{{ $value }}"
                maxlength="{{ $col->column_length }}"
                class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @else
            <input type="text"
                name="{{ $col->column_name }}"
                value="{{ $value }}"
                maxlength="{{ $col->column_length }}"
                placeholder="{{ $col->column_label ?? str()->headline($col->column_name) }}"
                class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @endif

        @error($col->column_name)
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>
@endforeach

{{-- Dynamic select fields (enum/set) --}}
@foreach($selectFields as $col)
    @php
        $options = $enumOptions[$col->column_name] ?? ($col->options ?? []);
        $value = old($col->column_name, $profile?->{$col->column_name} ?? '');
    @endphp
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $col->column_label ?? str()->headline($col->column_name) }}
            @if(!$col->is_nullable)<span class="text-red-500">*</span>@endif
        </label>
        <select name="{{ $col->column_name }}"
            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white">
            <option value="">Pilih {{ $col->column_label ?? str()->headline($col->column_name) }}</option>
            @foreach($options as $option)
                <option value="{{ $option }}" {{ $value === $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach
        </select>
        @error($col->column_name)
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>
@endforeach

{{-- Dynamic textarea fields (skip 'alamat' - shown separately) --}}
@foreach($textAreaFields as $col)
    @if($col->column_name !== 'alamat')
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                {{ $col->column_label ?? str()->headline($col->column_name) }}
                @if(!$col->is_nullable)<span class="text-red-500">*</span>@endif
            </label>
            <textarea name="{{ $col->column_name }}" rows="3"
                class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none">{{ old($col->column_name, $profile?->{$col->column_name} ?? '') }}</textarea>
            @error($col->column_name)
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    @endif
@endforeach

{{-- Dynamic file fields --}}
@foreach($fileFields as $col)
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $col->column_label ?? str()->headline($col->column_name) }}
        </label>
        <input type="file" name="{{ $col->column_name }}"
            accept=".jpg,.jpeg,.png,.pdf"
            class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-[#174E93] hover:file:bg-blue-100 cursor-pointer">
        <p class="text-xs text-gray-400 mt-1">{{ __('cms.pengguna.form_kartu_identitas_help') }}</p>
        @if($profile?->{$col->column_name})
            <p class="text-xs text-gray-600 mt-1">
                {{ __('cms.pengguna.form_kartu_identitas_current') }}:
                <a href="{{ asset('storage/' . $profile->{$col->column_name}) }}" target="_blank"
                    class="text-blue-600 hover:underline">{{ __('cms.pengguna.form_kartu_identitas_view') }}</a>
            </p>
        @endif
        @error($col->column_name)
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>
@endforeach

{{-- Alamat as full width at end --}}
@if($profileColumns->contains('column_name', 'alamat'))
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $profileColumns->firstWhere('column_name', 'alamat')->column_label ?? 'Alamat' }}
            @if(!$profileColumns->firstWhere('column_name', 'alamat')->is_nullable)<span class="text-red-500">*</span>@endif
        </label>
        <textarea name="alamat" rows="3"
            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none">{{ old('alamat', $profile?->alamat ?? '') }}</textarea>
        @error('alamat')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>
@endif
