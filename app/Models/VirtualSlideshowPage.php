<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualSlideshowPage extends Model
{
    protected $table = 'virtual_slideshow_pages';

    protected $fillable = [
        'feature_id',
        'title',
        'title_en',
        'description',
        'description_en',
        'order',
        'thumbnail_path',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function slideshowSlides()
    {
        return $this->hasMany(VirtualSlideshowSlide::class, 'feature_page_id')->orderBy('order');
    }
}
