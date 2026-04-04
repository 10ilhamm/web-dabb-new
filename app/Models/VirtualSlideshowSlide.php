<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualSlideshowSlide extends Model
{
    protected $fillable = [
        'feature_id',
        'feature_page_id',
        'slide_type',
        'title',
        'title_en',
        'subtitle',
        'subtitle_en',
        'description',
        'description_en',
        'images',
        'image_urls',
        'video_url',
        'video_file',
        'carousel_video_urls',
        'layout',
        'bg_color',
        'info_popup',
        'order',
    ];

    protected $casts = [
        'images'     => 'array',
        'image_urls' => 'array',
        'carousel_video_urls' => 'array',
        'info_popup' => 'array',
    ];

    public function getImagesAttribute($value)
    {
        if (is_array($value)) return $value;
        if (is_null($value) || $value === '' || $value === 'null') return [];
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    public function getImageUrlsAttribute($value)
    {
        if (is_array($value)) return $value;
        if (is_null($value) || $value === '' || $value === 'null') return [];
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    public function getCarouselVideoUrlsAttribute($value)
    {
        if (is_array($value)) return $value;
        if (is_null($value) || $value === '' || $value === 'null') return [];
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    public function getInfoPopupAttribute($value)
    {
        if (is_array($value)) return $value;
        if (is_null($value) || $value === '' || $value === 'null') return [];
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function featurePage()
    {
        return $this->belongsTo(FeaturePage::class);
    }

    public function slideshowPage()
    {
        return $this->belongsTo(VirtualSlideshowPage::class, 'feature_page_id');
    }
}
