<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profiles';

    protected $fillable = [
        'feature_id',
        'title',
        'title_en',
        'description',
        'description_en',
        'type',
        'subtitle',
        'subtitle_en',
        'link_text',
        'link_url',
        'logo_path',
        'chart_data',
        'images',
        'image_positions',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'image_positions' => 'array',
            'chart_data' => 'array',
        ];
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function sections()
    {
        return $this->hasMany(ProfileSection::class)->orderBy('order');
    }
}
