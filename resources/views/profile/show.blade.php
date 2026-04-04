@extends('layouts.app')

@section('header')
    <div class="text-[13px] text-gray-500 font-medium">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">{{ __('dashboard.header.breadcrumb_home') }}</a> /
        <span class="text-[#0ea5e9]">{{ __('dashboard.profile.manage_account') }}</span>
    </div>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-[22px] font-bold text-[#1E293B] mb-6">{{ __('dashboard.profile.user_profile') }}</h1>

        <!-- Top Card: Basic Info -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-6 flex items-center">
            <div class="flex items-center justify-center w-16 h-16 p-[3px] bg-white border border-gray-200 rounded-full shadow-sm shrink-0">
                <img class="w-full h-full rounded-full object-cover block"
                    src="{{ $user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=E5E7EB&color=374151&bold=true&size=128' }}"
                    alt="Avatar">
            </div>
            <div class="ml-5">
                <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                <div class="text-sm font-medium border-l-[3px] border-blue-200 pl-3 mt-1.5 flex items-center space-x-3">
                    @if(in_array($user->role, ['admin', 'pegawai']))
                    <span class="text-gray-400">NIP. {{ $user->profile?->nip ?? '-' }}</span>
                    <span class="text-gray-300">|</span>
                    @endif
                    <span class="text-gray-500">{{ App\Models\User::roleLabels()[$user->role] ?? __('dashboard.roles.default') }}</span>
                </div>
            </div>
        </div>

        <!-- Bottom Card: Details Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full relative">
            <div class="p-6 pb-20">
                <h3 class="text-base font-bold text-gray-800 mb-6">{{ __('dashboard.profile.personal_data') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8">
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">{{ __('dashboard.profile.full_name') }}
                        </div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->name }}</div>
                    </div>
                    
                    @if(in_array($user->role, ['admin', 'pegawai']))
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">{{ __('dashboard.profile.nip') }}</div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->profile?->nip ?? '-' }}</div>
                    </div>
                    @endif

                    @if($user->role !== 'instansi_swasta')
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">{{ __('dashboard.profile.gender') }}</div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->profile?->jenis_kelamin ?? '-' }}</div>
                    </div>
                    @endif

                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">{{ __('dashboard.profile.email') }}</div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->email }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">{{ __('dashboard.profile.phone_number') }}
                        </div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->profile?->nomor_whatsapp ?? '-' }}</div>
                    </div>
                    
                    @if(in_array($user->role, ['admin', 'pegawai']))
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">{{ __('dashboard.profile.religion') }}
                        </div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->profile?->agama ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">{{ __('dashboard.profile.position') }}
                        </div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->profile?->jabatan ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">{{ __('dashboard.profile.rank_class') }}
                        </div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->profile?->pangkat_golongan ?? '-' }}</div>
                    </div>
                    @endif

                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">
                            {{ __('dashboard.profile.birth_place_date') }}</div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->profile?->tempat_lahir ?? '-' }}, {{ $user->profile?->tanggal_lahir ? \Carbon\Carbon::parse($user->profile?->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</div>
                    </div>

                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">Nomor Kartu Identitas</div>
                        <div class="text-[13px] font-medium text-gray-800">{{ $user->profile?->nomor_kartu_identitas ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 mb-1">Kartu Identitas</div>
                        <div class="text-[13px] font-medium text-gray-800">
                            @if($user->profile?->kartu_identitas)
                            <a href="{{ Storage::url($user->profile->kartu_identitas) }}" target="_blank" class="text-blue-500 hover:underline">Lihat Dokumen</a>
                            @else
                            -
                            @endif
                        </div>
                    </div>



                    <div class="md:col-span-3">
                        <div class="text-[11px] font-medium text-gray-400 mb-1">{{ __('dashboard.profile.address') }}</div>
                        <div class="text-[13px] font-medium text-gray-800 leading-relaxed">
                            {{ $user->profile?->alamat ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button positioned absolutely within the card to match design -->
            <div class="absolute bottom-6 right-6">
                <a href="{{ route('profile.edit') }}"
                    class="bg-[#3B82F6] hover:bg-blue-600 text-white text-[13px] font-medium py-2 px-5 rounded-lg border border-blue-500 shadow-sm flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                    {{ __('dashboard.profile.edit') }}
                </a>
            </div>
        </div>
    </div>
@endsection
