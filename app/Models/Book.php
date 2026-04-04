<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'feature_id',
        'title',
        'title_en',
        'cover_image',
        'cover_position',
        'cover_scale',
        'cover_texts',
        'title_position',
        'back_title',
        'back_cover_image',
        'back_cover_position',
        'back_cover_scale',
        'back_title_position',
        'back_cover_texts',
        'thumbnail',
        'order',
    ];

    protected $casts = [
        'cover_position' => 'array',
        'cover_texts' => 'array',
        'title_position' => 'array',
        'back_cover_position' => 'array',
        'back_cover_texts' => 'array',
        'back_title_position' => 'array',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function pages()
    {
        return $this->hasMany(VirtualBookPage::class, 'book_id')->orderBy('order');
    }
}
