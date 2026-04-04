<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationService
{
    protected GoogleTranslate $translator;

    public function __construct()
    {
        $this->translator = new GoogleTranslate;
        $this->translator->setSource('id');
        $this->translator->setTarget('en');
    }

    /**
     * Translate a single string from Indonesian to English.
     * Returns original text if translation fails.
     */
    public function translate(string $text): string
    {
        if (trim($text) === '') {
            return $text;
        }

        try {
            return $this->translator->translate($text) ?? $text;
        } catch (\Exception $e) {
            Log::warning('Translation failed: '.$e->getMessage(), ['text' => mb_substr($text, 0, 100)]);

            return $text;
        }
    }

    /**
     * Recursively translate all string values in an array.
     * Skips keys that should not be translated (URLs, IDs, emails, etc).
     */
    public function translateArray(array $data, array $skipKeys = []): array
    {
        $defaultSkipKeys = ['youtube_ids', 'phone', 'email', 'address', 'copyright', 'path'];
        $skipKeys = array_merge($defaultSkipKeys, $skipKeys);
        $result = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $skipKeys, true)) {
                $result[$key] = $value;

                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->translateArray($value, $skipKeys);
            } elseif (is_string($value) && trim($value) !== '') {
                $result[$key] = $this->translate($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
