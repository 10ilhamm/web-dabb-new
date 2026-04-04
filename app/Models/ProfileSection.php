<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileSection extends Model
{
    protected $table = 'profile_sections';

    protected $fillable = [
        'profile_id',
        'title',
        'title_en',
        'description',
        'description_en',
        'images',
        'image_positions',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'image_positions' => 'array',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
