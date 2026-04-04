<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\TranslationService;

class SettingController extends Controller
{
    public function editFooter()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('cms.settings.footer', compact('settings'));
    }

    public function updateFooter(Request $request, TranslationService $translationService)
    {
        $data = $request->validate([
            'footer_title' => 'nullable|string',
            'footer_address' => 'nullable|string',
            'footer_phone' => 'nullable|string',
            'footer_email' => 'nullable|string',
            'footer_hours' => 'nullable|string',
            'footer_managed_by' => 'nullable|string',
            'footer_managed_by_sub' => 'nullable|string',
            'footer_facebook' => 'nullable|string',
            'footer_twitter' => 'nullable|string',
            'footer_instagram' => 'nullable|string',
            'footer_youtube' => 'nullable|string',
            'footer_map_embed' => 'nullable|string',
            'footer_menu_col1' => 'nullable|string',
            'footer_menu_col2' => 'nullable|string',
        ]);

        // Kunci yang perlu diterjemahkan otomatis ke bahasa Inggris
        $translatableKeys = [
            'footer_title',
            'footer_address',
            'footer_hours',
            'footer_managed_by',
            'footer_managed_by_sub',
            'footer_menu_col1',
            'footer_menu_col2',
        ];

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);

            // Translasi otomatis ke bahasa Inggris untuk field yang bisa diterjemahkan
            if (in_array($key, $translatableKeys)) {
                $enValue = !empty($value) ? $translationService->translate($value) : '';
                Setting::updateOrCreate(['key' => $key . '_en'], ['value' => $enValue]);
            }
        }

        return redirect()->back()->with('success', __('cms.common.saved_successfully') ?? 'Pengaturan berhasil disimpan.');
    }
    public function editDisclaimer()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('cms.settings.disclaimer', compact('settings'));
    }

    public function showDisclaimer()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $locale = app()->getLocale();

        $title = $locale === 'en'
            ? ($settings['disclaimer_title_en'] ?? $settings['disclaimer_title'] ?? 'Disclaimer')
            : ($settings['disclaimer_title'] ?? 'Disclaimer');

        $content = $locale === 'en'
            ? ($settings['disclaimer_content_en'] ?? $settings['disclaimer_content'] ?? '')
            : ($settings['disclaimer_content'] ?? '');

        return view('pages.disclaimer', compact('title', 'content', 'locale'));
    }

    public function updateDisclaimer(Request $request, TranslationService $translationService)
    {
        $data = $request->validate([
            'disclaimer_title' => 'nullable|string',
            'disclaimer_content' => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);

            // Translasi otomatis konten ke bahasa inggris jika diubah
            if ($key === 'disclaimer_content') {
                $htmlContent = $value;
                $base64Images = [];

                if (!empty($htmlContent)) {
                    // Gambar base64 ukurannya jutaan karakter (Mb), ini menyebabkan request Translasi API Google sangat lama.
                    // Oleh karena itu, kita ubah sementara base64 panjangnya menjadi teks kecil "src=LOCAL_IMAGE_X".
                    $htmlContent = preg_replace_callback('/src=["\']?data:image\/[^;]+;base64,[^"\']+["\']?/i', function($matches) use (&$base64Images) {
                        $id = count($base64Images);
                        $base64Images[$id] = $matches[0];
                        return 'src="LOCAL_IMAGE_' . $id . '"';
                    }, $htmlContent);

                    $enValue = $translationService->translate($htmlContent);

                    // Setelah terjemahan teks berhasil, kembalikan gambar aslinya ke konten.
                    foreach ($base64Images as $id => $imgData) {
                        $enValue = str_replace('src="LOCAL_IMAGE_' . $id . '"', $imgData, $enValue);
                    }
                } else {
                    $enValue = '';
                }

                Setting::updateOrCreate(['key' => 'disclaimer_content_en'], ['value' => $enValue]);
            }

            // Translasi otomatis title ke bahasa Inggris
            if ($key === 'disclaimer_title' && !empty($value)) {
                $enValue = $translationService->translate($value);
                Setting::updateOrCreate(['key' => 'disclaimer_title_en'], ['value' => $enValue]);
            }
        }

        return redirect()->back()->with('success', __('cms.common.saved_successfully') ?? 'Konten Disclaimer berhasil disimpan.');
    }

    public function uploadRteMedia(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;

            // Simpan gambar, video, pdf, atau dokumen lainnya ke folder public/storage/cms_media
            $path = $file->storeAs('cms_media', $filename, 'public');

            // Kembalikan URL absolut menggunakan url() agar Editor bisa merendernya
            $url = asset('storage/' . $path);

            return response()->json(['url' => $url]);
        }

        return response()->json(['error' => 'No file uploaded.'], 400);
    }
}
