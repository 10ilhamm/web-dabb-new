<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturePageSection extends Model
{
    protected $fillable = [
        'feature_page_id',
        'title',
        'title_en',
        'description',
        'description_en',
        'images',
        'image_positions',
        'order',
    ];

    protected $casts = [
        'images' => 'array',
        'image_positions' => 'array',
    ];

    /**
     * Get the images attribute with proper JSON decoding.
     */
    public function getImagesAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_null($value) || $value === '' || $value === 'null') {
            return null;
        }
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    /**
     * Get the image_positions attribute with proper JSON decoding.
     */
    public function getImagePositionsAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_null($value) || $value === '' || $value === 'null') {
            return null;
        }
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    public function featurePage()
    {
        return $this->belongsTo(FeaturePage::class);
    }
}
