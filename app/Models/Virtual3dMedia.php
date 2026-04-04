<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Virtual3dMedia extends Model
{
    protected $table = 'virtual3d_media';

    protected $fillable = [
        'virtual3d_room_id',
        'wall',
        'type',
        'file_path',
        'title',
        'description',
        'position_x',
        'position_y',
        'width',
        'height',
    ];

    public function room()
    {
        return $this->belongsTo(Virtual3dRoom::class, 'virtual3d_room_id');
    }
}
