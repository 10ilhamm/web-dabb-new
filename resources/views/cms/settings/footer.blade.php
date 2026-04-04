@extends('layouts.app')

@section('breadcrumb_items')
    <span class="text-gray-400">CMS</span>
@endsection
@section('breadcrumb_active', __('dashboard.sidebar.cms_footer'))

@section('content')
<div class="px-4 py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('dashboard.sidebar.cms_footer') ?? 'Pengaturan Footer' }}</h1>
    </div>


    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('cms.settings.footer.update') }}" method="POST" class="p-6 space-y-8">
            @csrf
            @method('PUT')

            <!-- Block 1: Map -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Kolom 1: Map / Peta</h3>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Kolom</label>
                        <input type="text" name="footer_title" value="{{ old('footer_title', $settings['footer_title'] ?? '') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Default: DEPOT ARSIP BERKELANJUTAN BANDUNG</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Embed URL Maps</label>
                        <textarea name="footer_map_embed" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('footer_map_embed', $settings['footer_map_embed'] ?? '') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Hanya masukkan URL bagian `src="..."` dari iFrame Google Maps Embed.</p>
                    </div>
                </div>
            </div>

            <!-- Block 2: Informasi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Kolom 2: Informasi Kontak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap</label>
                        <textarea name="footer_address" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('footer_address', $settings['footer_address'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                        <input type="text" name="footer_phone" value="{{ old('footer_phone', $settings['footer_phone'] ?? '') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Email</label>
                        <input type="email" name="footer_email" value="{{ old('footer_email', $settings['footer_email'] ?? '') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jam Kerja</label>
                        <textarea name="footer_hours" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Gunakan enter untuk membuat baris baru. Contoh:&#10;Senin-Kamis : 7:30 - 16:00&#10;Jumat : 7:30 - 16:30">{{ old('footer_hours', $settings['footer_hours'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Block 3: Menu -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Kolom 3: Menu Navigasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tautan Kolom Kiri</label>
                        <textarea name="footer_menu_col1" rows="5" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Beranda | /&#10;Layanan Publik | /layanan&#10;...">{!! old('footer_menu_col1', $settings['footer_menu_col1'] ?? "Beranda | /\nLayanan Publik | #\nPublikasi | #\nKontak Kami | #") !!}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Gunakan format <code class="bg-gray-100 px-1 rounded text-xs">Label | URL</code> per baris.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tautan Kolom Kanan</label>
                        <textarea name="footer_menu_col2" rows="5" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Profil | /profil&#10;...">{!! old('footer_menu_col2', $settings['footer_menu_col2'] ?? "Profil | #\nPameran Arsip | #\nPerpustakaan DABB | #\nDisclaimer | #") !!}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Gunakan format <code class="bg-gray-100 px-1 rounded text-xs">Label | URL</code> per baris.</p>
                    </div>
                </div>
            </div>

            <!-- Block 4: Dikelola Oleh -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Kolom 4: Pengelola & Sosial Media</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teks Pengelola (Tebal)</label>
                        <textarea name="footer_managed_by" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Depot Arsip&#10;Berkelanjutan Bandung">{{ old('footer_managed_by', $settings['footer_managed_by'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teks Pengelola (Kecil)</label>
                        <textarea name="footer_managed_by_sub" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Depot Arsip&#10;Berkelanjutan">{{ old('footer_managed_by_sub', $settings['footer_managed_by_sub'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link Facebook</label>
                        <input type="url" name="footer_facebook" value="{{ old('footer_facebook', $settings['footer_facebook'] ?? '') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://facebook.com/...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link X (Twitter)</label>
                        <input type="url" name="footer_twitter" value="{{ old('footer_twitter', $settings['footer_twitter'] ?? '') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://twitter.com/...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link Instagram</label>
                        <input type="url" name="footer_instagram" value="{{ old('footer_instagram', $settings['footer_instagram'] ?? '') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://instagram.com/...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link YouTube</label>
                        <input type="url" name="footer_youtube" value="{{ old('footer_youtube', $settings['footer_youtube'] ?? '') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://youtube.com/...">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-5 mt-8 border-t border-gray-100">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
